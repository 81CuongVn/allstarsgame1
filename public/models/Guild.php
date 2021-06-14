<?php
class Guild extends Relation {
	static $player_limit	= null;
	static $paranoid		= true;

	function filter($where, $page, $limit) {
		$result	= [];
		if (!$where) {
			$result['pages']	= floor(Recordset::query('SELECT MAX(id) AS _max FROM guilds')->row()->_max / $limit);
			$result['guilds']	= Guild::all(['limit' => ($page * $limit) . ', ' . $limit, 'reorder' => 'member_count ASC']);
		} else {
			$result['pages']	= floor(Recordset::query('SELECT COUNT(id) AS _max FROM (SELECT id FROM guilds WHERE 1=1 ' . $where . ') _w')->row()->_max / $limit);
			$result['guilds']	= Guild::find('1=1 ' . $where, ['limit' => ($page * $limit) . ', ' . $limit, 'reorder' => 'member_count ASC']);
		}

		return $result;
	}

	function after_create() {
		$leader						= new GuildPlayer();
		$leader->guild_id			= $this->id;
		$leader->player_id			= $this->player_id;
		$leader->can_accept_players	= 1;
		$leader->can_kick_players	= 1;
		$leader->save();
	}

	protected function before_update() {
		if ($this->is_next_level()) {
			while ($this->is_next_level()) {
				$this->exp			-= $this->level_exp();
				$this->level		+= 1;
			}

			// Guild upou, atualiza os bonus nos membros
			$members	= $this->players();
			foreach ($members as $member) {
				$member->_update_sum_attributes();
			}
		}
	}

	function level_rewards($level) {
		return GuildLevelReward::find_first('id = ' . $level);
	}

	function level_exp() {
		$exp = ((1000 / 5) * 8);
		if ($this->level) {
			$exp *= $this->level + 1;
		}
		return $exp * 1.5;
	}

	function is_next_level() {
		return $this->exp >= $this->level_exp() && $this->level < MAX_LEVEL_GUILD;
	}

	function faction() {
		return $this->leader()->faction();
	}

	function leader() {
		return Player::find($this->player_id);
	}

	function players() {
		return GuildPlayer::find('guild_id=' . $this->id);
	}

	function request($id) {
		return GuildRequest::find_first('guild_id=' . $this->id . ' AND id=' . $id);
	}

	function requests() {
		return GuildRequest::find('guild_id=' . $this->id);
	}

	function player($id) {
		return GuildPlayer::find_first('guild_id=' . $this->id . ' AND player_id=' . $id);
	}

	function can_accept_player($source_id, $target_id = null) {
		$source_player		= $this->player($source_id);
		$can_accept			= $source_player ? $source_player->can_accept_players : false;
		$reason				= new stdClass();
		$reason->allowed	= false;
		$reason->messages	= [];

		if (!$target_id) {
			if ($can_accept) {
				$reason->allowed	= true;
			}

			return $reason;
		}

		$target	= $this->request($target_id);

		if ($can_accept && $target) {
			$player	= $target->player();

			if ($player->guild_id) {
				$reason->messages[]	= t('guild.errors.already_in_other');
			} else {
				if (!is_null(Guild::$player_limit) && sizeof($this->players()) >= Guild::$player_limit) {
					$reason->messages[]	= t('guild.errors.max_players');
				} else {
					$reason->allowed	= true;
				}
			}
		} else {
			$reason->messages[]	= t('guild.errors.no_privilege');
		}

		return $reason;
	}

	function can_kick_player($source_id, $target_id = null) {
		$source_player		= $this->player($source_id);
		$can_kick			= $source_player ? $source_player->can_kick_players : false;
		$reason				= new stdClass();
		$reason->allowed	= false;
		$reason->messages	= [];

		if (!$target_id) {
			if ($can_kick) {
				$reason->allowed	= true;
			}

			return $reason;
		}

		$target	= $this->player($target_id);


		if ($can_kick && $target) {
			if ($target->player_id != $this->player_id) {
				# Future guild quest code check should be here
				$reason->allowed	= true;
			} else {
				$reason->messages[]	= t('guild.errors.cant_kick_leader');
			}
		} else {
			$reason->messages[]	= t('guild.errors.no_permission');
		}

		return $reason;
	}

	function fix_member_count() {
		$this->member_count	= sizeof($this->players());
		$this->save();
	}

	function check_event_finished($player) {
		$accepted		= GuildAcceptedEvent::find_first($player->guild_accepted_event_id);
		$active_event	= GuildEvent::find_first($accepted->guild_event_id);

		$npcs			= 0;
		$bosses			= 0;

		$objects		= GuildMapObjectSession::find("guild_id = {$this->id} and guild_accepted_event_id = {$accepted->id}");
		foreach ($objects as $object) {
			if ($object->player_id) {
				$npcs++;
			} else {
				$bosses++;
			}
		}

		$bypassed_timer	= false;
		if (now() > strtotime($accepted->finishes_at) ) {
			$bypassed_timer = true;
		}

		if ($bypassed_timer) {
			$accepted->finished_at = now(true);
			$accepted->save();

			// Remove os jogadores da dungeon
			$players = Player::find('guild_accepted_event_id = ' . $accepted->id);
			foreach ($players as $p) {
				$p->guild_accepted_event_id = 0;
				$p->save();
			}

			$this->guild_accepted_event_id = 0;
			$this->save();
		} else {
			if ($bosses >= $active_event->require_boss && $npcs >= $active_event->require_npc) {
				$accepted->finished_at = now(true);
				$accepted->won = 1;
				$accepted->save();

				// Remove os jogadores da dungeon
				$players = Player::find('guild_accepted_event_id = ' . $accepted->id);
				foreach ($players as $p) {
					$p->guild_accepted_event_id = 0;
					$p->save();
				}

				$this->guild_accepted_event_id = 0;
				$this->save();

				$reward = $active_event->reward();

				foreach ($this->players() as $guild_player) {
					$p		= $guild_player->player();
					$user	= $p->user();

					if ($reward->currency) {
						$p->earn($reward->currency);

						$p->achievement_check('currency');
						$p->check_objectives('currency');
					}

					if ($reward->exp) {
						$p->earn_exp($reward->exp);
					}

					if ($reward->credits) {
						$user->earn($reward->credits);

						$p->achievement_check("credits");
						$p->check_objectives("credits");
					}

					if ($reward->equipment) {
						if ($reward->equipment == 1) {
							$dropped  = Item::generate_equipment($p);
						} elseif ($reward->equipment == 2) {
							$dropped  = Item::generate_equipment($p, 0);
						} elseif ($reward->equipment == 3) {
							$dropped  = Item::generate_equipment($p, 1);
						} elseif ($reward->equipment == 4) {
							$dropped  = Item::generate_equipment($p, 2);
						} elseif ($reward->equipment == 5) {
							$dropped  = Item::generate_equipment($p, 3);
						}

						$p->achievement_check('equipment');
						$p->check_objectives('equipment');
					}

					if ($reward->item_id && $reward->pets) {
						$npc_pet = Item::find($reward->item_id);

						$player_pet = new PlayerItem();
						$player_pet->item_id = $npc_pet->id;
						$player_pet->player_id = $p->id;
						$player_pet->save();

						$p->achievement_check('pets');
						$p->check_objectives('pets');
					}

					if ($reward->item_id && !$reward->pets) {
						$player_item_exist = PlayerItem::find_first("item_id=" . $reward->item_id . " AND player_id=" . $p->id);
						if (!$player_item_exist) {
							$player_item			= new PlayerItem();
							$player_item->item_id	= $reward->item_id;
							$player_item->quantity	= $reward->quantity;
							$player_item->player_id	= $p->id;
							$player_item->save();
						} else {
							$player_item_exist->quantity += $reward->quantity;
							$player_item_exist->save();
						}
					}

					if ($reward->character_id && !$user->is_character_bought($reward->character_id)) {
						$reward_character = new UserCharacter();
						$reward_character->user_id		= $p->user_id;
						$reward_character->character_id	= $reward->character_id;
						$reward_character->was_reward	= 1;
						$reward_character->save();

						$p->achievement_check('character');
						$p->check_objectives('character');
					}

					if ($reward->character_theme_id && !$user->is_theme_bought($reward->character_theme_id)) {
						$reward_theme = new UserCharacterTheme();
						$reward_theme->user_id				= $p->user_id;
						$reward_theme->character_theme_id	= $reward->character_theme_id;
						$reward_theme->was_reward			= 1;
						$reward_theme->save();

						$p->achievement_check('character_theme');
						$p->check_objectives('character_theme');
					}

					if ($reward->headline_id && !$user->is_headline_bought($reward->headline_id)) {
						$reward_headline = new UserHeadline();
						$reward_headline->user_id		= $p->user_id;
						$reward_headline->headline_id	= $reward->headline_id;
						$reward_headline->save();
					}
				}
			}
		}

	}
}
