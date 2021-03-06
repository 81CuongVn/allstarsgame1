<?php
	class PlayerItem extends Relation {
		private	$_player	= null;

		function after_create() {
			$stat					= new PlayerItemStat();
			$stat->player_item_id	= $this->id;
			$stat->save();
		}

		function set_player($player) {
			$this->_player	= $player;
		}

		function player() {
			if(!$this->_player) {
				$instance	= Player::get_instance();

				if($this->player_id == $instance->id) {
					$this->_player	=& $instance;
				} else {
					$instance		= Player::find($this->player_id);
					$this->_player	=& $instance;
				}

				return $instance;				
			}

			return $this->_player;
		}

		function item() {
			$this->player();
			if($this->parent_id){
				$item	= Item::find($this->parent_id);
			}else{
				$item	= Item::find($this->item_id);
			}
			
			$item->set_player($this->_player);
			$item->set_player_item($this);

			if($item->is_generic || in_array($item->item_type_id, [5, 7])) {
				$item->set_anime($this->player()->character()->anime_id);
			} else {
				$item->set_character_theme($this->player()->character_theme());
			}

			$item->formula(true);

			return $item;
		}

		function stats() {
			return PlayerItemStat::find_first('player_item_id=' . $this->id);
		}

		function attributes() {
			return PlayerItemAttribute::find_first('player_item_id=' . $this->id);
		}
	}