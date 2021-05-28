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
    function upgrade_equipment($player, $rarity, $item_id, $slot, $method){
        // Zera os atributos do seu resumo geral
        $player_attribute		= PlayerAttribute::find_first('player_id='.$player->id);
        $player_item_attribute	= PlayerItemAttribute::find_first('player_item_id='.$item_id);

        $player_attribute->currency_battle 			    	-= $player_item_attribute->currency_battle;
        $player_attribute->exp_battle 			    		-= $player_item_attribute->exp_battle;
        $player_attribute->currency_quest 	    			-= $player_item_attribute->currency_quest;
        $player_attribute->exp_quest 	    				-= $player_item_attribute->exp_quest;
        $player_attribute->sum_bonus_luck_discount 	    	-= $player_item_attribute->luck_discount;
        $player_attribute->sum_bonus_drop		    		-= $player_item_attribute->item_drop_increase;
        $player_attribute->generic_technique_damage		    -= $player_item_attribute->generic_technique_damage;
        $player_attribute->unique_technique_damage	    	-= $player_item_attribute->unique_technique_damage;
        $player_attribute->defense_technique_extra  		-= $player_item_attribute->defense_technique_extra;
        $player_attribute->save();

        $player_item_attribute->currency_battle             = 0;
        $player_item_attribute->exp_battle                  = 0;
        $player_item_attribute->currency_quest              = 0;
        $player_item_attribute->exp_quest                   = 0;
        $player_item_attribute->luck_discount               = 0;
        $player_item_attribute->item_drop_increase          = 0;
        $player_item_attribute->generic_technique_damage    = 0;
        $player_item_attribute->unique_technique_damage     = 0;
        $player_item_attribute->defense_technique_extra     = 0;
        $player_item_attribute->save();
        // Zera os atributos do seu resumo geral

        $attributes_by_slot	= [
            'head'		=> [ 'generic_technique_damage','unique_technique_damage','defense_technique_extra','currency_battle','exp_battle','currency_quest','exp_quest','luck_discount','item_drop_increase' ],
            'shoulder'	=> [ 'generic_technique_damage','unique_technique_damage','defense_technique_extra','currency_battle','exp_battle','currency_quest','exp_quest','luck_discount','item_drop_increase' ],
            'chest'		=> [ 'generic_technique_damage','unique_technique_damage','defense_technique_extra','currency_battle','exp_battle','currency_quest','exp_quest','luck_discount','item_drop_increase' ],
            'neck'		=> [ 'generic_technique_damage','unique_technique_damage','defense_technique_extra','currency_battle','exp_battle','currency_quest','exp_quest','luck_discount','item_drop_increase' ],
            'hand'		=> [ 'generic_technique_damage','unique_technique_damage','defense_technique_extra','currency_battle','exp_battle','currency_quest','exp_quest','luck_discount','item_drop_increase' ],
            'leggings'	=> [ 'generic_technique_damage','unique_technique_damage','defense_technique_extra','currency_battle','exp_battle','currency_quest','exp_quest','luck_discount','item_drop_increase' ]
        ];
        $attributes_by_chances	= [
            '0'		    => [ 'generic_technique_damage', 80 ],
            '1'		    => [ 'unique_technique_damage',  90 ],
            '2'	    	=> [ 'defense_technique_extra',  70 ],
            '3'     	=> [ 'item_drop_increase',       60 ],
            '4'	    	=> [ 'luck_discount',            40 ],
            '5'	    	=> [ 'exp_battle',               20 ],
            '6'	    	=> [ 'exp_quest',                20 ],
            '7'	    	=> [ 'currency_quest',            1 ],
            '8'		    => [ 'currency_battle',           1 ]
        ];
        $bases	= [
			'generic_technique_damage'		=> [ 1, 3 ],
			'unique_technique_damage'		=> [ 1, 3 ],
			'defense_technique_extra'		=> [ 1, 3 ],
			'currency_battle'				=> [ 1, 5 ],
			'exp_battle'					=> [ 1, 5 ],
			'currency_quest'				=> [ 1, 5 ],
			'exp_quest'						=> [ 1, 5 ],
			'luck_discount'					=> [ 1, 5 ],
			'item_drop_increase'			=> [ 1, 2 ]
        ];

        $attributes = [];
        foreach ($attributes_by_chances AS $attributes_by_chance) {
            $random_number  = rand(1, 100);

			if ($_SESSION['universal']) {
				$random_number = 100;
			}

			if ($random_number >= $attributes_by_chance[1]) {
                array_push($attributes, $attributes_by_chance[0]);
			}
        }

		// Adiciona os novos valores
        $player_attribute		= PlayerAttribute::find_first('player_id='.$player->id);
        $player_item_attribute	= PlayerItemAttribute::find_first('player_item_id='.$item_id);
        // Adiciona os novos valores

        $array_keys = array_rand($attributes, $method);
        $count_keys = !is_array($array_keys) ? 1 : count($array_keys);
        for ($i = 0; $i < $count_keys; $i++) {
            // Correção por causa do array_rand();
            $array_key = $count_keys > 1 ? $array_keys[$i] : $array_keys;

            if ($attributes[$array_key] == "luck_discount") {
                $player_attribute_correct = "sum_bonus_luck_discount";
			} elseif ($attributes[$array_key] == "item_drop_increase") {
                $player_attribute_correct = "sum_bonus_drop";
			} else {
                $player_attribute_correct = $attributes[$array_key];
			}

            // Gera o numero randomico do update
            $random_valor = rand($bases[$attributes[$array_key]][0], $bases[$attributes[$array_key]][1]);

            $player_attribute->{$player_attribute_correct}    += $random_valor;
            $player_item_attribute->{$attributes[$array_key]} += $random_valor;
        }
        $player_item_attribute->save();
        $player_attribute->save();

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
                    switch ($player_item->rarity){
                        case 'common':		$rarity = 0;	break;
                        case 'rare':		$rarity = 1;	break;
                        case 'epic':		$rarity = 2;	break;
                        case 'legendary':	$rarity = 3;	break;
                    }

                    // Adiciona o contador de aprimoramentos
                    $upgrade_counter = PlayerStat::find_first("player_id=".$player->id);
                    if ($method == 1719) {
                        // $upgrade_counter->sands++;
                        // Verifica a conquista de fragmentos - Conquista
                        $player->achievement_check("sands");
                    } else {
                        // $upgrade_counter->bloods++;
                        // Verifica a conquista de fragmentos - Conquista
                        $player->achievement_check("bloods");
                    }
                    $upgrade_counter->save();
                    // Adiciona o contador de aprimoramentos

                    $count = $method == 1719 ? 1 : 2;
                    $upgrade = $this->upgrade_equipment($player, $rarity, $item_id, $player_item->slot_name, $count);
                }
                // Só faz para o Sangue e Areia

                if ($method == 1852 || $method == 1853) {
                    // Destroi os equipamentos na Player Item e na Player Item Atributtes
                    if ($method == 1852) {
                        $item_slot = $player_item->slot_name;
                        $item_raridade = 1;
                        $player_item->destroy();
                        $player_item->save();
                    } else {
                        $item_slot = $player_item->slot_name;
                        $item_raridade = 2;
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
            if($position->slot_name == $_POST['slot']) {
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
                if($position->slot_name == $_POST['slot']) {
                    $is_valid	= true;
                    break;
                }
            }

            if ($is_valid) {
                $player_item	= PlayerItem::find_first('id=' . $_POST['equipment'] . ' AND player_id=' . $player->id . ' AND slot_name="' . $_POST['slot'] . '" AND equipped=0');

                if(!$player_item) {
                    $errors[]	= t('equipments.equip.errors.invalid') . '[1]';
                } else {
                    $attributes	= $player_item->attributes();

                    if ($attributes->graduation_sorting > $player->graduation()->sorting) {
                        $errors[]	= t('equipments.equip.errors.graduation');
                    }
                }
            } else {
                $errors[]	= t('equipments.equip.errors.invalid') . '[2]';
            }
        } else {
            $errors[]	= t('equipments.equip.errors.invalid') . '[3]';
        }

        if (!sizeof($errors)) {
            $player->equip_equipment($player_item, $_POST['slot']);

            //Verifica a conquista de fragmentos - Conquista
            $player->achievement_check("equipment");

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

            if(!$player_item) {
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
