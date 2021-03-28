<?php
class Organization extends Relation {
	static $player_limit	= null;
	static $paranoid		= true;

	function filter($where, $page, $limit) {
		$result	= [];

		if(!$where) {
			$result['pages']			= floor(Recordset::query('SELECT MAX(id) AS _max FROM organizations')->row()->_max / $limit);
			$result['organizations']	= Organization::all(['limit' => ($page * $limit) . ', ' . $limit, 'reorder' => 'member_count ASC']);
		} else {
			$result['pages']			= floor(Recordset::query('SELECT COUNT(id) AS _max FROM (SELECT id FROM organizations WHERE 1=1 ' . $where . ') _w')->row()->_max / $limit);
			$result['organizations']	= Organization::find('1=1 ' . $where, ['limit' => ($page * $limit) . ', ' . $limit, 'reorder' => 'member_count ASC']);
		}

		return $result;
	}

	function after_create() {
		$leader						= new OrganizationPlayer();
		$leader->organization_id	= $this->id;
		$leader->player_id			= $this->player_id;
		$leader->can_accept_players	= 1;
		$leader->can_kick_players	= 1;
		$leader->save();
	}

	function faction() {
		return $this->leader()->faction();
	}

	function leader() {
		return Player::find($this->player_id);
	}

	function players() {
		return OrganizationPlayer::find('organization_id=' . $this->id);
	}

	function request($id) {
		return OrganizationRequest::find_first('organization_id=' . $this->id . ' AND id=' . $id);
	}

	function requests() {
		return OrganizationRequest::find('organization_id=' . $this->id);
	}

	function player($id) {
		return OrganizationPlayer::find_first('organization_id=' . $this->id . ' AND player_id=' . $id);
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

			if ($player->organization_id) {
				$reason->messages[]	= t('organization.errors.already_in_other');
			} else {
				if (!is_null(Organization::$player_limit) && sizeof($this->players()) >= Organization::$player_limit) {
					$reason->messages[]	= t('organization.errors.max_players');
				} else {
					$reason->allowed	= true;
				}
			}
		} else {
			$reason->messages[]	= t('organization.errors.no_privilege');
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
				# Future organization quest code check should be here
				$reason->allowed	= true;
			} else {
				$reason->messages[]	= t('organization.errors.cant_kick_leader');
			}
		} else {
			$reason->messages[]	= t('organization.errors.no_permission');
		}

		return $reason;
	}

	function fix_member_count() {
		$this->member_count	= sizeof($this->players());
		$this->save();
	}

	function check_event_finished($player) {
		$accepted		= OrganizationAcceptedEvent::find_first($player->organization_accepted_event_id);
		$active_event	= OrganizationEvent::find_first($accepted->organization_event_id);

		$objects = OrganizationMapObjectSession::find("organization_id={$this->id} AND organization_accepted_event_id={$accepted->id}");
		$npcs = 0;
		$bosses = 0;
		$bypassed_timer = false;

		foreach($objects as $object) {
			if ($object->player_id) {
				$npcs++;
			} else {
				$bosses++;
			}
		}

		if (now() > strtotime('YmdHis', $active_event->finishes_at) ) {
			$bypassed_timer = true;
		}

		if ($bypassed_timer) {
			$active_event->finished_at = now(true);
			$active_event->save();

			$this->organization_accepted_event_id = 0;
			$this->save();
		} else {
			if ($bosses >= $active_event->require_boss && $npcs >= $active_event->require_npc) {
				$active_event->finished_at = now(true);
				$active_event->won = 1;
				$active_event->save();

				$this->organization_accepted_event_id = 0;
				$this->save();

				$reward = $active_event->reward();

				foreach ($this->players() as $organization_player) {
					$p = $organization_player->player();

					if ($reward->currency) {
						$p->earn($reward->currency);
					}

					if ($reward->exp) {
						$p->earn_exp($reward->exp);
					}

					if ($reward->vip) {
						$p->user()->earn($reward->vip);
						$p->achievement_check("credits");
						$p->check_objectives("credits");
					}

					if ($reward->equipment) {
						if ($reward->equipment == 1) {
							$dropped  = Item::generate_equipment($p);
						} elseif ($reward->equipment==2) {
							$dropped  = Item::generate_equipment($p,0); 
						} elseif ($reward->equipment==3) {
							$dropped  = Item::generate_equipment($p,1); 
						} elseif ($reward->equipment==4) {
							$dropped  = Item::generate_equipment($p,2); 
						}
					}

					if ($reward->item_id && $reward->pets) {
						$npc_pet = Item::find($reward->item_id);
						
						$player_pet = new PlayerItem();
						$player_pet->item_id = $npc_pet->id;
						$player_pet->player_id = $p->id;
						$player_pet->save();
					}

					if ($reward->item_id && !$reward->pets) {
						$player_item_exist = PlayerItem::find_first("item_id=" . $reward->item_id . " AND player_id=" . $p->id);
						if(!$player_item_exist){
							$player_item = new PlayerItem();
							$player_item->item_id	= $reward->item_id;
							$player_item->quantity	= $reward->quantity;
							$player_item->player_id	= $p->id;
							$player_item->save();
						}else{
							$player_item_exist->quantity += $reward->quantity;
							$player_item_exist->save();
						}
					}

					if ($reward->character_id) {
						$reward_character = new UserCharacter();
						$reward_character->user_id = $p->user_id;
						$reward_character->character_id	= $reward->character_id;
						$reward_character->was_reward	= 1;
						$reward_character->save();
					}

					if ($reward->character_theme_id) {
						$reward_theme = new UserCharacterTheme();
						$reward_theme->user_id = $p->user_id;
						$reward_theme->character_theme_id	= $reward->character_theme_id;
						$reward_theme->was_reward = 1;
						$reward_theme->save();
					}

					if ($reward->headline_id) {
						$reward_headline = new UserHeadline();
						$reward_headline->user_id = $p->user_id;
						$reward_headline->headline_id = $reward->headline_id;
						$reward_headline->save();
					}
				}
			}
		}
		
	}
}