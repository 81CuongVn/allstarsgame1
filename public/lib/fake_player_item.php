<?php
	class FakePlayeritem {
		private $player;
		private	$item;

		public	$quantity	= 0;
		public	$level		= 1;
		public	$item_id	= 0;

		function __construct($id, $player) {
			$this->id		= $id;
			$this->player	= $player;
			$this->item_id	= $id;
			$item			= Item::find($id);
			$item->set_player($this->player);
			$item->set_player_item($this);

			if($item->is_generic || in_array($item->item_type_id, [5, 7])) {
				$item->set_anime($player->character()->anime_id);
			} else {
				$item->set_character_theme($player->character_theme());
			}

			$item->formula(true);

			$this->item	= $item;
		}

		function item() {
			return $this->item;
		}

		function set_player($player) {
			
		}

		function player() {
			return $this->player;
		}

		function stats() {
			$stats							= new stdClass();
			$stats->exp						= 0;
			$stats->uses					= 0;
			$stats->use_with_precision		= 0;
			$stats->use_low_stat			= 0;
			$stats->kills					= 0;
			$stats->kills_with_crit			= 0;
			$stats->kills_with_precision	= 0;
			$stats->full_defenses			= 0;

			return $stats;
		}
	}