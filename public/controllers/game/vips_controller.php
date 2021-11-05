<?php
class VipsController extends Controller {
	public function __construct() {
		$this->allowed_items	= Item::find("item_type_id = 9", [
			'cache' => true
		]);

		parent::__construct();
	}

	public function index() {
		$player				= Player::get_instance();
		$player_vip_items   = PlayerStarItem::find("player_id = {$player->id} and item_id = 429 and character_id != 0 and character_id != " . $player->character_id);

		$this->assign("vips",				$this->allowed_items);
		$this->assign("player",				$player);
		$this->assign("player_vip_items",	$player_vip_items);
		$this->assign("animes",				Anime::find('active = 1', ['reorder' => 'id ASC']));
		$this->assign("factions",			Faction::find('active = 1', ['reorder' => 'id ASC']));
	}

	public function no_talent() {
		$this->as_json		= true;
		$this->json->sucess	= false;
		$player				= Player::get_instance();
		$errors				= [];

		if (isset($_POST["id"]) && is_numeric($_POST["id"])) {
			$player_item = PlayerItem::find_first("player_id = {$player->id} and item_id = " . $_POST["id"]);
			if (!$player_item->quantity) {
				$errors[]	= t("vips.errors.dont_have");
			}

			if (!$player->has_item(1715)) {
				$errors[]	= t("vips.errors.invalid_item");
			}
		} else {
			$errors[]	= t("vips.errors.invalid_item");
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;

			$player->no_talent	= ($player->no_talent ? 0 : 1);
			$player->save();
		} else {
			$this->json->messages	= $errors;
		}
	}

	public function buy() {
		$this->as_json		= true;
		$this->json->sucess	= false;

		$player				= Player::get_instance();
		$user				= User::get_instance();
		$errors				= [];
		$item_found			= false;
		$buy_mode			= 0;

		if (isset($_POST["id"]) && is_numeric($_POST["id"])) {
			foreach ($this->allowed_items as $instance) {
				if ($instance->id == $_POST["id"]) {
					$item_found	= true;
					break;
				}
			}

			if (!$item_found) {
				$errors[]	= t("vips.errors.invalid_item");
			} else {
				$item		= Item::find($_POST["id"]);

				$item_431	= false;
				if ($item->id == 431) {
					$item_431 			= PlayerStarItem::find_first("player_id = {$player->id} and item_id = 431");

					$buy_mode			= 1;
					$bought_free		= false;
					$bought_currency	= false;

					if ($player->currency < $item->price_currency) {
						$errors[]	= t("vips.errors.not_enough_currency", [
							'currency'	=> t('currencies.' . $player->character()->anime_id)
						]);
					}
				} elseif (in_array($item->id, [1709, 1715, 1718, 2112, 2113, 2114, 2115, 2116])) {
					$buy_mode			= 2;
					$bought_free		= false;
					$bought_currency	= true;
				} else {
					$bought_free		= PlayerStarItem::find_first("player_id=" . $player->id . " AND item_id=" . $item->id . " AND buy_mode = 0");
					$bought_currency	= PlayerStarItem::find_first("player_id=" . $player->id . " AND item_id=" . $item->id . " AND buy_mode = 1");
				}

				// Trocar de Personagem
				if ($item->id == 429) {
					$character	= Character::find($_POST['character_id'], [ 'cache' => true ]);
					if (!$character->unlocked($user)) {
						$errors[]	= t('characters.create.errors.locked');
					}
				}

				// Já pegou o gratuito, cobrar em ouro
				if ($bought_free && !$bought_currency) {
					$buy_mode = 1;

					if ($player->currency < $item->price_currency) {
						$errors[]	= t("vips.errors.not_enough_currency", [
							'currency'	=> t('currencies.' . $player->character()->anime_id)
						]);
					}
				}

				// Já comprou em ouro, cobrar em estrelas (deu merda)
				if ($bought_currency) {
					$buy_mode = 2;

					if ($user->credits < $item->price_credits) {
						$errors[]	= t("vips.errors.not_enough_vip");
					}
				}

				// Trocar de Personagem
				// Memorização de Personagem
				if (isset($_POST["character_id"]) && !is_numeric($_POST["character_id"])) {
					$errors[]	= t("vips.errors.invalid_character");
				}

				// Recuperar 50% de Stamina
				if ($item_431) {
					$errors[]	= "Você já usou esse item hoje";
				}

				// Memorização de Personagem
				if ($_POST['id'] == 1864) {
					if (!isset($_POST['character_id'])) {
						$errors[]	= "Você não pode usar esse item sem ter nenhum personagem memorizado.";
					} else {
						$player_vip_items = PlayerStarItem::find_first("player_id=".$player->id." AND item_id=429 AND character_id=".$_POST['character_id']);
						if (!$player_vip_items) {
							$errors[]	= "Você não tem esse personagem memorizado.";
						}
					}
				}

				// Trocar de Nome da Organização
				if (isset($_POST["name_guild"])) {
					if ($player->guild_id) {
						$player_guild = Guild::find_first('id='.$player->guild_id);
						if ($player_guild->player_id != $player->id) {
							$errors[]	= t('guilds.errors.not_leader');
						}

						if (!between(strlen(trim($_POST['name_guild'])), 6, 20) || !preg_match(REGEX_GUILD, $_POST['name_guild'])) {
							$errors[]	= t('guilds.create.errors.invalid_name');
						}
					}
				}

				// Troca de Facção
				if (isset($_POST["faction"])) {
					$faction = Faction::find($_POST["faction"]);
					if (!$faction || !$faction->active) {
						$errors[]	= t("vips.errors.invalid_faction");
					}

					if ($player->guild_id) {
						$errors[]	= t('guilds.create.errors.change');
					}
				}

				// Trocar de Nome
				if (isset($_POST["name"])) {
					if (!preg_match(REGEX_PLAYER, $_POST['name'])) {
						$errors[]	= t('characters.create.errors.invalid_name');
					}

					if (Player::find('name="' . addslashes($_POST['name']) . '"')) {
						$errors[]	= t('characters.create.errors.existent');
					}

					if (strlen(trim($_POST['name'])) > 14) {
						$errors[]	= t('characters.create.errors.name_length_max');
					}

					if (strlen(trim($_POST['name'])) < 6) {
						$errors[]	= t('characters.create.errors.name_length_min');
					}
				}
			}
		} else {
			$errors[]	= t("vips.errors.invalid_item");
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;

			// Debita o valor do jogador
			if ($buy_mode == 1) {
				$player->spend($item->price_currency);
			} elseif ($buy_mode == 2) {
				$user->spend($item->price_credits);
			}

			if (in_array($item->id, [2113, 2114, 2115, 2116])) {
				$player->add_vip_item($item);
			}

			/* Adicionar log da compra */
			$bought					= new PlayerStarItem();
			$bought->item_id		= $item->id;
			$bought->player_id		= $player->id;
			$bought->buy_mode		= $buy_mode;
			if ($_POST['id'] == 429 || $_POST['id'] == 1864) {
				$bought->character_id = $_POST["character_id"];
			}
			$bought->save();

			switch ($_POST['id']) {
				case 427:	// Resetar Talentos
					$talents = array_map(function ($item) { return $item->id; }, Item::find("item_type_id=6"));
					PlayerItem::destroy_all("player_id=" . $player->id . " AND item_id IN(" . implode(",", $talents) . ")");
					break;
				case 428:	// Resetar Atributos
					$attributes = [
						"for_atk", "for_def", "for_crit", "for_abs" ,
						"for_prec", "for_init", "for_inc_crit", "for_inc_abs"
					];
					foreach ($attributes as $attribute) {
						$player->$attribute = 0;
					}
					$player->training_points_spent	= 0;
					$player->save();
					break;
				case 429:	// Trocar de Personagem
				case 1864:	// Memorização de Personagem
					$character	= Character::find($_POST['character_id']);

					/* Faz a troca do personagem */
					$player->character_id				= $_POST['character_id'];
					$player->character_theme_id			= $character->themes()[0]->id;
					$player->character_theme_image_id	= $character->themes()[0]->images()[0]->id;
					$player->character_ability_id		= CharacterAbility::find_first("character_id = {$player->character_id} and is_initial = 1", ['cache' => true])->id;
					$player->character_speciality_id	= CharacterSpeciality::find_first("character_id = {$player->character_id} and is_initial = 1", ['cache' => true])->id;
					$player->save();

					/* Remove as Habilidades */
					$player_character_abilities = PlayerCharacterAbility::find("player_id = " . $player->id);
					foreach ($player_character_abilities as $player_character_ability) {
						$player_character_ability->destroy();
					}

					/* Remove as Especialidades */
					$player_character_specialities = PlayerCharacterSpeciality::find("player_id = " . $player->id);
					foreach ($player_character_specialities as $player_character_speciality) {
						$player_character_speciality->destroy();
					}

					// Adiciona as Habilidades do jogador
					$character_abilities = CharacterAbility::find("character_id = " . $_POST['character_id']);
					foreach ($character_abilities as $character_ability){
						$player_character_ability = new PlayerCharacterAbility();
						$player_character_ability->player_id			= $player->id;
						$player_character_ability->character_ability_id	= $character_ability->id;
						$player_character_ability->character_id			= $player->character_id;
						$player_character_ability->item_effect_ids		= $character_ability->item_effect_ids;
						$player_character_ability->effect_chances		= $character_ability->effect_chances;
						$player_character_ability->effect_duration		= $character_ability->effect_duration;
						$player_character_ability->consume_mana			= $character_ability->consume_mana;
						$player_character_ability->cooldown				= $character_ability->cooldown;
						$player_character_ability->is_initial			= $character_ability->is_initial;
						$player_character_ability->save();

					}

					// Adiciona as Especialidades do jogador
					$character_specialities = CharacterSpeciality::find("character_id = " . $_POST['character_id']);
					foreach ($character_specialities as $character_speciality) {
						$player_character_speciality = new PlayerCharacterSpeciality();
						$player_character_speciality->player_id					= $player->id;
						$player_character_speciality->character_speciality_id	= $character_speciality->id;
						$player_character_speciality->character_id				= $player->character_id;
						$player_character_speciality->item_effect_ids			= $character_speciality->item_effect_ids;
						$player_character_speciality->effect_chances			= $character_speciality->effect_chances;
						$player_character_speciality->effect_duration			= $character_speciality->effect_duration;
						$player_character_speciality->consume_mana				= $character_speciality->consume_mana;
						$player_character_speciality->cooldown					= $character_speciality->cooldown;
						$player_character_speciality->is_initial				= $character_speciality->is_initial;
						$player_character_speciality->save();
					}
					break;
				case 430:	// Trocar de Nome
					$player->name	= $_POST['name'];
					$player->save();
					break;
				case 431:	// Recuperar 50% da Stamina
					if (!$player->less_stamina == 0) {
						$stamina = percent(50, $player->for_stamina(true));
						$player->less_stamina	= ($player->less_stamina - $stamina) <= 0 ? 0 : $player->less_stamina - $stamina;
						$player->save();
					}
					break;
				case 432:	// Recuperar 100% da Stamina
					$player->less_stamina	= 0;
					$player->save();
					break;
				case 1709:	// Slot de Personagem Extra
					$user->character_slots += 1;
					$user->save();
					break;
				case 1715:	// Sem Talentos
					if (!$player->has_item(1715)) {
						$player_item				= new PlayerItem();
						$player_item->item_id		= 1715;
						$player_item->player_id		= $player->id;
						$player_item->quantity		= 5;
						$player_item->save();
					} else {
						$player_item  = PlayerItem::find_first("player_id = {$player->id} and item_id = 1715");
						$player_item->quantity	+= 5;
						$player_item->save();
					}
					break;
				case 1718:	// Troca de Estrelas por Moedas
					$player->currency	+= 2000;
					$player->save();
					break;
				case 1745:	// Trocar de Nome da Organização
					$player_guild->name	= $_POST["name_guild"];
					$player_guild->save();
					break;
				case 1746:	// Troca de Facção
					$player->faction_id = $faction->id;
					$player->save();
					break;
				case 2112:	// Fragmento de Almas
					$item_446 = PlayerItem::find_first("player_id = {$player->id} and item_id = 446");
					if ($item_446) {
						$item_446->quantity += 100;
						$item_446->save();
					} else {
						$player_fragment			= new PlayerItem();
						$player_fragment->item_id	= 446;
						$player_fragment->player_id	= $player->id;
						$player_fragment->quantity 	= 100;
						$player_fragment->save();
					}
					break;
			}
		} else {
			$this->json->messages	= $errors;
		}

	}

	public function make_donation() {
		$is_dbl		= StarDouble::find_first('NOW() BETWEEN data_init AND data_end');
		$methods	= [
			// 'mercadopago'	=> 'BRL',
			'pagseguro'		=> 'BRL',
			'paypal_eur'	=> 'EUR',
			'paypal_usd'	=> 'USD',
			// 'paypal_brl'	=> 'BRL'
		];

		ksort($methods);
		$symbols	= [
			'BRL'			=> 'R$',
			'EUR'			=> '€',
			'USD'			=> '$',
		];

		$this->assign("is_dbl",		$is_dbl);
		$this->assign("methods",	$methods);
		$this->assign("symbols",	$symbols);
		$this->assign("plans",		StarPlan::all());
		$this->assign("player",		Player::get_instance());
	}

	public function pay_donation() {
		$user = User::get_instance();

		$this->as_json			= TRUE;
		$this->json->success	= FALSE;
		$errors					= [];

		if (!isset($_POST['mode']) || (isset($_POST['mode']) && !is_numeric($_POST['mode']))) {
			$errors[]	= t('vips.errors.plan_invalid');
		} else {
			$star_plan = StarPlan::find_first("id=" . $_POST['mode']);
			if (!$star_plan) {
				$errors[]	= t('vips.errors.plan_invalid');
			}
		}

		if (!sizeof($errors)) {
			// Adiciona o Plano Vip na tabela de aguarde do Usuário
			$star_purchase = new StarPurchase();
			$star_purchase->user_id 		= $user->id;
			$star_purchase->star_plan_id 	= $star_plan->id;
			$star_purchase->star_method		= $_POST['valor'];
			$star_purchase->save();

			$this->json->success	= TRUE;
		} else {
			$this->json->errors	= $errors;
		}
	}

	public function done_donation() {
		$this->render	= false;
        $this->layout	= false;

		$user 		= User::get_instance();
		$star_purchase	= StarPurchase::find_first("user_id = {$user->id} and completed_at is null", [
			'reorder'	=> 'id desc'
		]);

		if ($star_purchase) {
			$star_plan	= StarPlan::find_first("id = " . $star_purchase->star_plan_id);
			$coins		= [
				// 'mercadopago'	=> 'BRL',
				'pagseguro'		=> 'BRL',
				'paypal_eur'	=> 'EUR',
				'paypal_usd'	=> 'USD',
				// 'paypal_brl'	=> 'BRL'
			];

			// Bbusca o preço
			$price	= 'price_' . strtolower($coins[$star_purchase->star_method]);
			$price	= $star_plan->$price;
			if ($_SESSION['universal']) {
				$price = 1;
			}

			switch ($star_purchase->star_method) {
				case 'mercadopago':
					if (MP_SAMDBOX) {
						MercadoPago\SDK::setAccessToken(MP_SAMDBOX_TOKEN);
					} else {
						MercadoPago\SDK::setAccessToken(MP_PROD_TOKEN);
					}

					// Cria um objeto de preferência
					$preference	= new MercadoPago\Preference();
					$preference->back_urls				= [
						'success'	=> make_url('vips/make_donation?success'),
						'failure'	=> make_url('vips/make_donation?failure'),
						'pending'	=> make_url('vips/make_donation?pending')
					];
					$preference->auto_return			= 'approved';

					// Cria um item na preferência
					$item				= new MercadoPago\Item();
					$item->id			= $star_plan->id;
					$item->title		= GAME_PREFIX . ' - ' . $star_plan->name;
					$item->description	= $star_plan->description;
					$item->quantity		= 1;
					$item->unit_price	= $price;
					$item->currency_id	= $coins[$star_purchase->star_method];

					// Adiciona os itens na preferência e salva
					$preference->items					= [ $item ];
					$preference->statement_descriptor	= GAME_PREFIX;
					$preference->external_reference		= $star_purchase->id;
					$preference->notification_url		= make_url('callback/mercadopago?source_news=ipn');
					$preference->save();

					$callback_url	= !MP_SAMDBOX ? 'init_point' : 'sandbox_init_point';

					header("Location: " . $preference->$callback_url);

					break;
				case 'pagseguro':
					\PagSeguro\Library::initialize();
					\PagSeguro\Library::cmsVersion()->setName(GAME_NAME)->setRelease(GAME_VERSION);
					\PagSeguro\Library::moduleVersion()->setName(GAME_NAME)->setRelease(GAME_VERSION);

					$payment = new \PagSeguro\Domains\Requests\Payment();
					$payment->addItems()->withParameters(
						$star_plan->id,
						GAME_PREFIX . ' - ' . $star_plan->name,
						1,
						$price
					);
					$payment->setCurrency($coins[$star_purchase->star_method]);
					$payment->setReference($star_purchase->id);
					$payment->setRedirectUrl(make_url('vips/make_donation'));
					$payment->setNotificationUrl(make_url('callback/pagseguro'));
					try {
						$result = $payment->register(
							\PagSeguro\Configuration\Configure::getAccountCredentials()
						);

						header("Location: " . $result);
					} catch (Exception $e) {
						redirect_to('vips/make_donation?failed');
					}
					break;
				default:
					$p = new PayPal();

					$p->addField('business',		PAYPAL_EMAIL);
					$p->addField('return',			make_url('vips/make_donation?success'));
					$p->addField('cancel_return',	make_url('vips/make_donation?cancel'));
					$p->addField('notify_url',		make_url('callback/paypal'));
					$p->addField('item_name',		GAME_PREFIX . ' - ' . $star_plan->name);
					$p->addField('currency_code',	$coins[$star_purchase->star_method]);
					$p->addField('amount',			$price);
					$p->addField('custom',			$star_purchase->id);

					if (PAYPAL_SANDBOX) {
						$p->useSandbox();
					}

					$p->submitPayment();
					break;
			}
		}
	}
}
