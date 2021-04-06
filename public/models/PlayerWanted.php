<?php
	class PlayerWanted extends Relation {
		static function filter($page, $limit) {
			$result	= [];

			$result['pages']	= ceil(Recordset::query('SELECT count(id) AS _max FROM player_wanteds WHERE death = 0')->row()->_max / $limit);
			$result['players']	= PlayerWanted::find('death = 0', [
				'limit'		=> ($page * $limit) . ', ' . $limit,
				'reorder'	=> 'player_id ASC'
			]);

			return $result;
		}
		function embed(){
			$attributes2	= Player::find_first($this->player_id);
			$embed		= [
				'name'			=> $attributes2->name,
				'type'			=> t('wanted.'.$this->type_death),
				'won'			=> ($attributes2->won_last_battle > 100 ? 100 * 250 : $attributes2->won_last_battle * 250 ) .' '. t('currencies.' . $attributes2->character()->anime_id),
				'character'		=> $attributes2->character_id,
				'id'			=> $this->player_id
			];

            $iv = substr(CHAT_KEY, 0, 16);
            return openssl_encrypt(json_encode($embed), 'AES-256-CBC', CHAT_KEY, 0, $iv);
		}
		function chat_embed() {
			return '';
		}
	}