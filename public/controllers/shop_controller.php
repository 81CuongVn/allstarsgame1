<?php
	class ShopController extends Controller {
		function map(){
			$player				= Player::get_instance();
			$result 			= array();
			$products			= MapStore::find("is_store=1");
			$player_items 		= PlayerItem::find("item_id in (1721,1851) AND player_id=". $player->id);
			
			foreach ($products as $product) {
				array_push($result,$product->item_id);
			}
			$items = Item::find("id in (".implode(",",$result).")");
			
			$this->assign('player', $player);
			$this->assign('player_items', $player_items);
			$this->assign('items', $items);
		}
		function food() {
			$player	= Player::get_instance();

			$this->assign('discount', $player->attributes()->sum_bonus_food_discount);
			$this->assign('player', $player);
			$this->assign('items', $player->character()->consumables(true));
		}

		function weapons() {
			$player	= Player::get_instance();

			$this->assign('discount', $player->attributes()->sum_bonus_weapon_discount);
			$this->assign('player', $player);
			$this->assign('items', $player->character_theme()->weapons());
		}

		function buy() {
			$this->layout			= false;
			$this->as_json			= true;
			$this->render			= false;
			$this->json->success	= false;
			$player					= Player::get_instance();
			$user					= User::get_instance();
			$errors					= array();

			if(isset($_POST['item']) && is_numeric($_POST['item']) && isset($_POST['method']) && is_numeric($_POST['method']) && isset($_POST['quantity']) && $_POST['quantity'] >= 1) {
				$item			= Item::find($_POST['item']);
				$discount		= $item->item_type_id == 5 ? $player->attributes()->sum_bonus_food_discount : $player->attributes()->sum_bonus_weapon_discount;
				$price_currency	= $item->price_currency - percent($discount, $item->price_currency);

				if(!$item || ($item && !in_array($item->item_type_id, [5, 7]))) {
					$errors[]	= t('shop.errors.invalid');
				} else {
					$methods	= array();

					if($item->price_currency) { $methods[]	= 1; }
					if($item->price_credits) { $methods[]	= 2; }

					if(!in_array($_POST['method'], $methods)) {
						$errors[]	= t('shop.errors.method');
					} else {
						if($_POST['method'] == 1 && ($price_currency * $_POST['quantity']) > $player->currency) {
							$errors[]	= t('shop.errors.enough_currency');
						}

						if($_POST['method'] == 2 && ($item->price_credits * $_POST['quantity']) > $user->credits) {
							$errors[]	= t('shop.errors.enough_credits');
						}
					}
				}
			} else {
				$errors[]	= t('shop.errors.invalid');
			}

			if(!sizeof($errors)) {
				$player_item			= $player->add_consumable($item, $_POST['quantity']);

				$this->json->success	= true;
				$this->json->quantity	= $player_item->quantity;
				$this->json->message	= t('shop.bought');

				if($item->price_credits) {
					$user->spend($item->price_credits * $_POST['quantity']);
				}

				if($item->price_currency) {
					$player->spend($price_currency * $_POST['quantity']);
				}

				$this->json->currency	= $player->currency;
				$this->json->credits	= $user->credits;
			} else {
				$this->json->errors		= $errors;
			}
		}
		function map_buy() {
			$this->as_json			= true;
			$this->json->success	= false;
			$errors					= [];
			$player					= Player::get_instance();
			$user					= User::get_instance();

			if(isset($_POST['item']) && is_numeric($_POST['item'])) {
				$mapstore = MapStore::find_first("item_id=".$_POST['item']." AND is_store=1");
				if(!$mapstore) {
					$errors[]	= t('shop.errors.invalid');
				} else {
					if($mapstore->anime_id==1){
						$player_item = PlayerItem::find_first("player_id=".$player->id." AND item_id=1721");
						if($player_item){
							if($player_item->quantity < $mapstore->map_item_total ){
								$errors[]	= t('shop.errors.dont_have_money');
							}else{
								$player_item->quantity -= $mapstore->map_item_total;
								$player_item->save();
							}
						}else{
							$errors[]	= t('shop.errors.dont_have_money');
						}	
						
					}elseif($mapstore->anime_id==9){
						$player_item = PlayerItem::find_first("player_id=".$player->id." AND item_id=1851");
						if($player_item){
							if($player_item->quantity < $mapstore->map_item_total ){
								$errors[]	= t('shop.errors.dont_have_money');
							}else{
								$player_item->quantity -= $mapstore->map_item_total;
								$player_item->save();
							}
						}else{
							$errors[]	= t('shop.errors.dont_have_money');
						}
						
					}		
			
				}
			} else {
				$errors[]	= t('shop.errors.invalid');
			}

			if(!sizeof($errors)) {
				$item = Item::find_first($mapstore->item_id); 
				$player->add_consumable($item, $mapstore->quantity);

				$this->json->success	= true;
			} else {
				$this->json->errors		= $errors;
			}
		}
	}