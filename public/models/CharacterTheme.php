<?php
	class CharacterTheme extends Relation {
		static	$always_cached	= true;

		function anime() {
			return $this->character()->anime();
		}

		function character() {
			return Character::find($this->character_id, array('cache' => true));
		}

		function description() {
			return CharacterThemeDescription::find_first('character_theme_id=' . $this->id, array('cache' => true));
		}

		function header_image($path_only = false) {
			$character	= $this->character();
			$path		= 'headers/' . $character->id . '/' . $this->theme_code . '/1.jpg';

			if($path_only) {
				return $path;
			} else {
				return '<img src="' . image_url($path) . '" alt="' . $character->description()->name . '" />';
			}
		}

		function profile_image($path_only = false) {
			$character	= $this->character();
			$path		= 'profile/' . $character->id . '/' . $this->theme_code . '/1.jpg';

			if($path_only) {
				return $path;
			} else {
				return '<img src="' . image_url($path) . '" alt="' . $character->description()->name . '" />';
			}
		}
		function small_image($path_only = false) {
			$character	= $this->character();
			$path		= 'criacao/' . $character->id . '/' . $this->theme_code . '/1.jpg';

			if($path_only) {
				return $path;
			} else {
				return '<img src="' . image_url($path) . '" alt="' . $character->description()->name . '" />';
			}
		}
		function small_image2($path_only = false) {
			$character	= $this->character();
			$path		= 'criacao/' . $character->id . '/' . $this->theme_code . '/1.jpg';

			if($path_only) {
				return $path;
			} else {
				return '<img src="' . image_url($path) . '" alt="' . $character->description()->name . '" width="75"/>';
			}
		}
		function images() {
			return CharacterThemeImage::find('character_theme_id=' . $this->id, array('cache' => true));
		}
		
		function first_image() {
			return $this->images()[0];
		}

		function attacks($unique = false) {
			$anime_id	= $this->character()->anime_id;
			$result		= [];
			$items		= Recordset::query('
				SELECT
					a.item_id

				FROM
					character_theme_items a JOIN
					items b ON b.id=a.item_id

				WHERE
					character_theme_id=' . $this->id . ' AND
					b.item_type_id = 1 AND
					language_id=' . $_SESSION['language_id'] . '

				ORDER BY b.mana_cost ASC
				', true);

			foreach ($items->result_array() as $item) {
				$instance	= Item::find($item['item_id'], array('cache' => true));
				$instance->set_character_theme($this);

				$result[]	= $instance;
			}

			if (!$unique) {
				$items	= Recordset::query('
					SELECT
						a.item_id

					FROM
						item_descriptions a JOIN
						items b ON b.id=a.item_id

					WHERE
						a.anime_id=' . $anime_id . ' AND
						b.item_type_id = 1 AND b.bonus_stamina_consume = 0 AND b.parent_id = 0 AND
						b.is_generic = 1 AND
						language_id=' . $_SESSION['language_id'], true);

				foreach ($items->result_array() as $item) {
					$instance	= Item::find($item['item_id'], array('cache' => true));
					$instance->set_anime($anime_id);

					$result[]	= $instance;
				}
			}

			$final	= array();
			$inc	= 0.001;

			foreach($result as $item) {
				$index					= (float)$item->mana_cost + $inc;
				$final[(string)$index]	= $item;

				$inc	+= 0.001;
			}

			ksort($final);

			return $final;
		}

		function weapons() {
			$result		= array();
			$items		= Item::find('item_type_id=7', array('cache' => true));
			$anime_id	= $this->character()->anime_id;

			foreach ($items as $instance) {
				$instance->set_anime($anime_id);
				$result[]	= $instance;
			}

			return $result;
		}
	}