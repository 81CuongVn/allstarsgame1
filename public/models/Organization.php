<?php
class Organization extends Relation {
	static $player_limit = null;
	static $paranoid = true;

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

	
}