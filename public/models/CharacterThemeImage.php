<?php
	class CharacterThemeImage extends Relation {
		static	$always_cached	= true;

		function character_theme() {
			return CharacterTheme::find($this->character_theme_id, array('cache' => true));
		}

		function profile_image($path_only = false) {
			$theme		= $this->character_theme();
			$character	= $theme->character();
			$extension	= $this->ultimate ? 'gif' : 'jpg';
			$path		= 'profile/' . $character->id . '/' . $theme->theme_code . '/' . $this->image . '.' . $extension;

			if($path_only) {
				return $path;
			} else {
				// if ($this->ultimate) {
				// 	$timestamp	= filemtime(ROOT . '/assets/images/' . $path);

				// 	return '<embed height="281" width="235" src="' . image_url($path) . '?_cache=' . $timestamp . '" quality="high" wmode="transparent" allowscriptaccess="always" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash"></embed>';
				// } else {
					return '<img src="' . image_url($path) . '" alt="' . $character->description()->name . '" />';
				// }
			}
		}
		
		function small_image($path_only = false) {
			$theme		= $this->character_theme();
			$character	= $theme->character();
			$path		= 'criacao/' . $character->id . '/' . $theme->theme_code . '/1.jpg';

			if($path_only) {
				return $path;
			} else {
				return '<img src="' . image_url($path) . '" alt="' . $character->description()->name . '" width="80"/>';
			}
		}
	}