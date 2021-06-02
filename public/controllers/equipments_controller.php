<?php
class EquipmentsController extends Controller {
    function index() {
        $player	= Player::get_instance();
        $anime	= $player->character()->anime();

        $this->assign('positions', $anime->equipment_positions());
        $this->assign('anime', $anime);
        $this->assign('player', $player);
        $this->assign('player_tutorial', $player->player_tutorial());
    }
    function list_equipments() {
        $this->layout	= false;
        $player			= Player::get_instance();
        $item_1719 		= PlayerItem::find_first("player_id =". $player->id. " AND item_id=1719");
        $item_1720		= PlayerItem::find_first("player_id =". $player->id. " AND item_id=1720");
        $item_1852		= PlayerItem::find_first("player_id =". $player->id. " AND item_id=1852");
        $item_1853		= PlayerItem::find_first("player_id =". $player->id. " AND item_id=1853");

        if ($_POST) {
            $this->as_json			= true;
            $this->render			= false;
            $this->json->success	= false;
            $errors					= [];

            if (is_numeric($_POST['id']) && is_numeric($_POST['method'])) {
                $item_id 	= $_POST['id'];
                $method 	= $_POST['method'];

                $player_item_attribute	= PlayerItemAttribute::find_first('player_item_id='.$item_id);
                $player_item			= PlayerItem::find_first('id='.$item_id);

                if (!$method) {
                    $errors[]	= t('upgrade.errors.4');
                } else {
                    if ($method == 1719) {
                        if ($item_1719) {
                            if ($item_1719->quantity >= 1) {
                                if ($item_1719->quantity == 1) {
                                    $item_1719->quantity = 0;
                                } else {
                                    $item_1719->quantity--;
                                }
                                $item_1719->save();
                            } else {
                                $errors[]	= t('upgrade.errors.2');
                            }
                        } else {
                            $errors[]	= t('upgrade.errors.2');
                        }
                    }

					if ($method == 1720) {
                        if ($item_1720) {
                            if ($item_1720->quantity >= 1) {
                                if ($item_1720->quantity == 1) {
                                    $item_1720->quantity = 0;
                                } else {
                                    $item_1720->quantity--;
                                }
                                $item_1720->save();
                            } else {
                                $errors[]	= t('upgrade.errors.3');
                            }
                        } else {
                            $errors[]	= t('upgrade.errors.3');
                        }
                    }

					if ($method == 1852) {
                        if ($item_1852) {
                            if ($item_1852->quantity >= 1) {
                                if($item_1852->quantity == 1) {
                                    $item_1852->quantity = 0;
                                } else {
                                    $item_1852->quantity--;
                                }
                                $item_1852->save();
                            } else {
                                $errors[]	= t('upgrade.errors.3');
                            }
                        } else {
                            $errors[]	= t('upgrade.errors.3');
                        }
                    }

					if ($method == 1853) {
                        if ($item_1853) {
                            if ($item_1853->quantity >= 1) {
                                if ($item_1853->quantity == 1) {
                                    $item_1853->quantity = 0;
                                } else {
                                    $item_1853->quantity--;
                                }
                                $item_1853->save();
                            } else {
                                $errors[]	= t('upgrade.errors.3');
                            }
                        } else {
                            $errors[]	= t('upgrade.errors.3');
                        }
                    }
                }

				if (!$player_item_attribute) {
                    $errors[]	= t('upgrade.errors.1');
                }

                if (!$player_item) {
                    $errors[]	= t('upgrade.errors.1');
                }
            } else {
                $errors[]	= t('upgrade.errors.1');
            }

            if (!sizeof($errors)) {
                // Só faz para o Sangue e Areia
                if ($method == 1719 || $method == 1720) {
                    // Adiciona o contador de aprimoramentos
                    $upgrade_counter = PlayerStat::find_first("player_id=".$player->id);
                    if ($method == 1719) {
                        $upgrade_counter->sands++;

						// Verifica a conquista de fragmentos - Conquista
                        $player->achievement_check("sands");
						$player->check_objectives("sands");
                    } elseif ($method == 1720) {
                        $upgrade_counter->bloods++;

						// Verifica a conquista de fragmentos - Conquista
                        $player->achievement_check("bloods");
						$player->check_objectives("bloods");
                    }
                    $upgrade_counter->save();
                    // Adiciona o contador de aprimoramentos

                    $count = $method == 1719 ? 1 : 2;
                    $upgrade = Item::upgrade_equipment($player, $item_id, $count);
                }
                // Só faz para o Sangue e Areia

                if ($method == 1852 || $method == 1853) {
                    // Destroi os equipamentos na Player Item e na Player Item Atributtes
                    if ($method == 1852) {
                        $item_slot		= $player_item->slot_name;
                        $item_raridade	= 1;
                        $player_item->destroy();
                        $player_item->save();
                    } else {
                        $item_slot		= $player_item->slot_name;
                        $item_raridade	= 2;
                        $player_item->destroy();
                        $player_item->save();
                    }

					$player_item_attribute->destroy();
                    $player_item_attribute->save();

                    // Gera o novo equipamento
                    Item::generate_equipment($player, $item_raridade, $item_slot);

                    // Faz o equipamento novo vir equipado
                    $last_player_item = PlayerItem::find_first("player_id=".$player->id." AND item_id=114 ORDER BY id DESC LIMIT 1");
                    $player->equip_equipment($last_player_item, $item_slot);

                }
                $this->json->success  = true;
            } else {
                $this->json->errors	= $errors;
            }
        } else {
            $equipments = PlayerItem::find('player_id=' . $player->id . ' AND item_id=114 AND id='.$_GET['id']);

            $this->assign('equipments', $equipments);
            $this->assign('item_1719', $item_1719);
            $this->assign('item_1720', $item_1720);
            $this->assign('item_1852', $item_1852);
            $this->assign('item_1853', $item_1853);
        }

    }
    function show() {
        $this->layout	= false;

        $player			= Player::get_instance();
        $anime			= $player->character()->anime();
        $positions		= $anime->equipment_positions();
        $is_valid		= false;

        foreach ($positions as $position) {
            if ($position->slot_name == $_POST['slot']) {
                $is_valid	= true;
                break;
            }
        }

        if ($is_valid) {
            $this->assign('equipments', $player->get_equipments($_POST['slot'], true));
        }

        $this->assign('is_valid', $is_valid);
    }

    function equip() {
        $this->as_json			= true;
        $this->json->success	= false;
        $this->json->messages	= [];

		$player					= Player::get_instance();
        $anime					= $player->character()->anime();
        $positions				= $anime->equipment_positions();
        $is_valid				= false;
        $errors					= [];

        if (isset($_POST['equipment']) && is_numeric($_POST['equipment'])) {
            foreach ($positions as $position) {
                if ($position->slot_name == $_POST['slot']) {
                    $is_valid	= true;
                    break;
                }
            }

            if ($is_valid) {
                $player_item	= PlayerItem::find_first('id=' . $_POST['equipment'] . ' AND player_id=' . $player->id . ' AND slot_name = "' . $_POST['slot'] . '"');
                if (!$player_item) {
                    $errors[]	= t('equipments.equip.errors.invalid');
                } else {
                    $attributes	= $player_item->attributes();
                    if ($attributes->graduation_sorting > $player->graduation()->sorting) {
                        $errors[]	= t('equipments.equip.errors.graduation');
                    }
                }
            } else {
                $errors[]	= t('equipments.equip.errors.invalid');
            }
        } else {
            $errors[]	= t('equipments.equip.errors.invalid');
        }

        if (!sizeof($errors)) {
			if (!$player_item->equipped) {
            	$player->equip_equipment($player_item, $_POST['slot']);

            	// Verifica a conquista de fragmentos - Conquista
            	$player->achievement_check("equipment");
				$player->check_objectives("equipment");
			} else {
				$player->unequip_equipment($player_item);
			}

            $this->json->success	= true;
        } else {
            $this->json->messages	= $errors;
        }
    }
    function destroy() {
        $this->as_json			= true;
        $this->json->success	= false;
        $this->json->messages	= [];
        $player					= Player::get_instance();
        $errors					= [];

        if (isset($_POST['equipment']) && is_numeric($_POST['equipment'])) {
            $player_item	= PlayerItem::find_first('id=' . $_POST['equipment'] . ' AND player_id=' . $player->id . ' AND equipped=0');

            if(!$player_item) {
                $errors[]	= t('equipments.equip.errors.invalid') . '[1]';
            }
        } else {
            $errors[]	= t('equipments.equip.errors.invalid') . '[2]';
        }

        if (!sizeof($errors)) {
            $item_446 = PlayerItem::find_first('item_id=446 AND player_id=' . $player->id);

            switch ($player_item->rarity) {
                case "common":		$destroy = 20;	break;
                case "rare":		$destroy = 40;	break;
                case "epic":		$destroy = 80;	break;
                case "legendary":	$destroy = 160;	break;
            }
            if($item_446){
                $item_446->quantity += $destroy;
                $item_446->save();
            }else{
                $item_446				= new PlayerItem();
                $item_446->item_id		= 446;
                $item_446->player_id	= $player->id;
                $item_446->quantity 	+= $destroy;
                $item_446->save();
            }
            // Verifica a conquista de fragmentos - Conquista
            $player->achievement_check("fragments");
			$player->check_objectives("fragments");

            $player_item->attributes()->destroy();
            $player_item->destroy();

            $this->json->success	= true;
        } else {
            $this->json->messages	= $errors;
        }
    }
    function sell() {
        $this->as_json			= true;
        $this->json->success	= false;
        $this->json->messages	= [];
        $player					= Player::get_instance();
        $errors					= [];

        if (isset($_POST['equipment']) && is_numeric($_POST['equipment'])) {
            $player_item	= PlayerItem::find_first('id=' . $_POST['equipment'] . ' AND player_id=' . $player->id . ' AND equipped=0');
            if (!$player_item) {
                $errors[]	= t('equipments.equip.errors.invalid') . '[1]';
            }
        } else {
            $errors[]	= t('equipments.equip.errors.invalid') . '[2]';
        }

        if (!sizeof($errors)) {
            $player->earn($player_item->item()->equipment_sell_price());

            $player_item->attributes()->destroy();
            $player_item->destroy();

            $this->json->success	= true;
        } else {
            $this->json->messages	= $errors;
        }
    }

    function get_names() {
        $this->as_json	= true;
        $player			= Player::get_instance();
        $techniques		= $player->character_theme()->attacks();
        $return			= [];

        foreach ($techniques as $technique) {
            $return[$technique->id]	= $technique->description()->name;
        }

        $this->json->techniques	= $return;
    }
}
