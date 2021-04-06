<?php
class Character extends Relation {
	static $always_cached	= true;

	public function description() {
		return CharacterDescription::find_first('character_id=' . $this->id . ' AND language_id=' . $_SESSION['language_id'], array('cache' => true));
	}
	public function anime() {
		return Anime::find($this->anime_id, array('cache' => true));
	}
	public function themes($extra = NULL) {
		return CharacterTheme::find('character_id=' . $this->id . $extra, array('cache' => true));
	}
	public function themes_default($id) {
		return CharacterTheme::find('character_id=' . $id.' AND is_default=1', array('cache' => true));
	}
	public function profile_image($path_only = FALSE) {
		$theme	= $this->default_theme();
		$path	= 'profile/' . $this->id . '/' . ($theme ? $theme->theme_code : 'X') . '/1.jpg';

		if ($path_only) {
			return $path;
		} else {
			return '<img src="' . image_url($path) . '" alt="' . $this->description()->name . '" />';
		}
	}
	public function small_image($path_only = false) {
		$theme	= $this->default_theme();
		$path	= 'criacao/' . $this->id . '/' . ($theme ? $theme->theme_code : 'X') . '/1.jpg';

		if ($path_only) {
			return $path;
		} else {
			return '<img src="' . image_url($path) . '" alt="' . $this->description()->name . '" />';
		}
	}
	public function small_image2($path_only = false) {
		$theme	= $this->default_theme();
		$path	= 'criacao/' . $this->id . '/' . ($theme ? $theme->theme_code : 'X') . '/1.jpg';

		if ($path_only) {
			return $path;
		} else {
			return '<img src="' . image_url($path) . '" alt="' . $this->description()->name . '"  width="75"/>';
		}
	}
	public function default_theme() {
		return CharacterTheme::find_first('is_default=1 AND character_id=' . $this->id, array('cache' => true));
	}
	public function tree() {
		$result	= [];
		$items	= Recordset::query("
			SELECT
				a.item_id
			FROM
				item_descriptions a JOIN
				items b ON b.id=a.item_id
			WHERE
				anime_id = {$this->anime_id} AND
				b.item_type_id = 2 AND
				language_id = " . $_SESSION['language_id'], TRUE);
		foreach ($items->result_array() as $item) {
			$result[]	= Item::find($item['item_id']);
		}

		return $result;
	}
	public function consumables($shop = FALSE) {
		$addSql = '';
		if ($shop) {
			$addSql = 'and id not in (1859, 1863, 2102, 2103, 2058, 2059)';
		}

		$result		= [];
		$anime_id	= $this->anime_id;
		$items		= Item::find('item_type_id = 5 ' . $addSql . ' order by price_currency asc', ['cache' => TRUE]);
		foreach ($items as $instance) {
			$instance->set_anime($anime_id);
			$result[]	= $instance;
		}

		return $result;
	}
	public function specialities($id) {
		return PlayerCharacterSpeciality::find('character_id=' . $this->id.' and player_id='.$id.' ORDER BY id');
	}
	public function specialities2() {
		return CharacterSpeciality::find('character_id=' . $this->id, ['cache' => true]);
	}
	public function abilities($id) {
		return PlayerCharacterAbility::find('character_id=' . $this->id.' and player_id='.$id.' ORDER BY id');
	}
	public function abilities2() {
		return CharacterAbility::find('character_id=' . $this->id, ['cache' => true]);
	}
	public function unlocked($user) {
		if ($this->reward_lock || $this->credits_lock || $this->currency_lock) {
			return UserCharacter::find('user_id=' . $user->id . ' AND character_id=' . $this->id);
		} else {
			return true;
		}
	}
	public static function filter($where, $mine_pets, $player, $active, $page, $limit) {
		$mine_pets2	= $mine_pets;
		$mine_pets	= implode(",", array_keys($mine_pets));
		
		if (!$active) {
			$where2 = "";	
			$where3 = "";	
		} elseif ($active == 1) {
			if (!$mine_pets) {
				$mine_pets = 0;	
			}
			
			$where2 = " AND c.player_id = ". $player ." AND c.item_id in (". $mine_pets .")";
			$where3 = " JOIN player_items c ON c.item_id = b.id";
		} elseif ($active == 2) {
			if (!$mine_pets) {
				$mine_pets = 0;	
			}
			$where2 = " AND b.parent_id = 0 AND a.item_id not in (". $mine_pets . ")";
			$where3 = "";
		}
		$result		= [];
		
		$result['pages']  = ceil(Recordset::query('
			SELECT
				COUNT(b.id) AS _max    

			FROM
				item_descriptions a JOIN
				items b ON b.id = a.item_id 
				' . $where3 . '

			WHERE
				1 = 1 AND b.parent_id = 0 AND b.item_type_id = 3 ' . $where . $where2, TRUE)->row()->_max / $limit);
														
		$result['pets']	= Recordset::query('
			SELECT
				a.item_id,
				a.name,
				a.description,
				b.rarity,
				b.parent_id       
			
			FROM
				item_descriptions a JOIN
				items b ON b.id = a.item_id 
				' . $where3 . '

			WHERE
				1 = 1 AND b.item_type_id = 3 ' . $where . $where2 . " LIMIT " . $page * $limit . "," . $limit);
				
							
		foreach ($result['pets']->result_array() as $item) {
			if (array_key_exists($item['item_id'], $mine_pets2)) {
				$result[] = Item::find($item['item_id']);
			} else {
				$can		= TRUE;
				$all_items	= ItemDescription::find('image=" ' .$item['item_id'] . '.png"');
				foreach ($all_items as $all_item) {
					if (array_key_exists($all_item->item_id, $mine_pets2)) {
						$can = FALSE;
					}
				}

				if ($can && $item['parent_id'] == 0) {
					$result[] = Item::find_first('id='.$item['item_id']);
				}
			}
		}

		return $result;
	}
}