<?php
class VipsController extends Controller {
	function __construct() {
		$this->allowed_items	= Item::find("item_type_id=9", ["cache" => true]);

		parent::__construct();
	}
	function index() {
		$player				= Player::get_instance();
		$player_vip_items   = PlayerStarItem::find("player_id=".$player->id." AND item_id=429 AND character_id !=0 AND character_id != ".$player->character_id);
		
		$this->assign("vips",				$this->allowed_items);
		$this->assign("player",				$player);
		$this->assign("player_vip_items",	$player_vip_items);
		$this->assign("animes",				Anime::find($_SESSION['universal'] ? '1=1' : 'active=1', ['reorder' => 'id ASC']));
		$this->assign("factions",			Faction::find($_SESSION['universal'] ? '1=1' : 'active=1', ['reorder' => 'id ASC']));
	}
	function no_talent(){
		$this->as_json		= true;
		$this->json->sucess	= false;
		$player				= Player::get_instance();
		$errors				= [];

		if (isset($_POST["id"]) && is_numeric($_POST["id"])) {
			$player_item = PlayerItem::find_first("player_id=".$player->id." AND item_id=".$_POST["id"]);
			
			if(!$player_item->quantity){
				$errors[]	= t("vips.errors.dont_have");
			}
			
			if(!$player->has_item(1715)){
				$errors[]	= t("vips.errors.invalid_item");
			}
		} else {
			$errors[]	= t("vips.errors.invalid_item");
		}
		
		if (!sizeof($errors)) {
			$this->json->success	= true;	
			$player->no_talent  = ($player->no_talent ? 0 : 1);
			$player->save();
		} else {
			$this->json->messages	= $errors;
		}	
	}
	function buy() {
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
				$item				= Item::find($_POST["id"]);
				$item_431 = false;
				
				if($item->id == 431){
					$item_431 			= PlayerStarItem::find_first("player_id=" . $player->id . " AND item_id=431");
					
					$buy_mode = 1;
					$bought_free = false;
					$bought_currency = false;
					
					if ($player->currency < $item->price_currency) {
						$errors[]	= t("vips.errors.not_enough_currency", [
							'currency'	=> t('currencies.' . $player->character()->anime_id)
						]);
					}
					
				} else if($item->id == 432 || $item->id == 1709 || $item->id == 1715 || $item->id == 1718 || $item->id == 1746  || $item->id == 2112){
					$buy_mode = 2;
					$bought_free = false;
					$bought_currency = true;
				} else{
					$bought_free		= PlayerStarItem::find_first("player_id=" . $player->id . " AND item_id=" . $item->id . " AND buy_mode = 0");
					$bought_currency	= PlayerStarItem::find_first("player_id=" . $player->id . " AND item_id=" . $item->id . " AND buy_mode = 1");
				}

				if ($item->id == 429) {
					$character	= Character::find($_POST['character_id'], ['cache' => true]);

					if (!$character->unlocked($user)) {
						$errors[]	= t('characters.create.errors.locked');
					}
				}

				if ($bought_free && !$bought_currency) {
					$buy_mode = 1;

					if ($player->currency < $item->price_currency) {
						$errors[]	= t("vips.errors.not_enough_currency", [
							'currency'	=> t('currencies.' . $player->character()->anime_id)
						]);
					}
				}

				if ($bought_currency) {
					$buy_mode = 2;

					if ($user->credits < $item->price_credits) {
						$errors[]	= t("vips.errors.not_enough_vip");
					}
				}

				if (isset($_POST["character_id"]) && !is_numeric($_POST["character_id"])) {
					$errors[]	= t("vips.errors.invalid_character");
				}
				if($item_431){
					$errors[]	= "Você já usou esse item hoje";
				}
				if($_POST['id']==1864){
					if(!isset($_POST['character_id'])){
						$errors[]	= "Você não pode usar esse item sem ter nenhum personagem memorizado.";
					}else{
						$player_vip_items = PlayerStarItem::find_first("player_id=".$player->id." AND item_id=429 AND character_id=".$_POST['character_id']);
						if(!$player_vip_items){
							$errors[]	= "Você não tem esse personagem memorizado.";
						}
					}
				}
				if (isset($_POST["name_organization"])) {
					if($player->organization_id){
						$player_organization = Organization::find_first('id='.$player->organization_id);
					
						if($player_organization->player_id != $player->id){
							$errors[]	= t('organizations.errors.not_leader');
						}
						if (!between(strlen($_POST['name_organization']), 6, 20) || !preg_match(REGEX_GUILD, $_POST['name_organization'])) {
							$errors[]	= t('organizations.create.errors.invalid_name');
						}
					}
				}
				if (isset($_POST["faction"])) {
					$faction = Faction::find($_POST["faction"]);
					if (!$faction || !$faction->active) {
						$errors[]	= t("vips.errors.invalid_faction");
					}
					if ($player->organization_id) {
						$errors[]	= t('organizations.create.errors.change');
					}
				}
				if (isset($_POST["name"])) {
					if (!preg_match(REGEX_PLAYER, $_POST['name'])) {
						$errors[]	= t('characters.create.errors.invalid_name');
					}

					if(Player::find('name="' . addslashes($_POST['name']) . '"')) {
						$errors[]	= t('characters.create.errors.existent');
					}

					if(strlen($_POST['name']) > 14) {
						$errors[]	= t('characters.create.errors.name_length_max');
					}

					if(strlen($_POST['name']) < 6) {
						$errors[]	= t('characters.create.errors.name_length_min');
					}
				}
			}
		} else {
			$errors[]	= t("vips.errors.invalid_item");
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;

			if ($buy_mode == 1) {
				$player->spend($item->price_currency);
			} elseif($buy_mode == 2) {
				$user->spend($item->price_credits);
			}
			
			
			$bought					= new PlayerStarItem();
			$bought->item_id		= $item->id;
			$bought->player_id		= $player->id;
			$bought->buy_mode		= $buy_mode;
			if($_POST['id']==429 || $_POST['id']==1864){
				$bought->character_id = $_POST["character_id"];
			}
			$bought->save();

			switch ($_POST['id']) {
				case 427:
					$talents = array_map(function ($item) { return $item->id; }, Item::find("item_type_id=6"));

					PlayerItem::destroy_all("player_id=" . $player->id . " AND item_id IN(" . implode(",", $talents) . ")");

					break;

				case 428:
					$attributes = ["for_atk", "for_def", "for_crit", "for_abs" ,
						"for_prec", "for_init", "for_inc_crit", "for_inc_abs"];

					foreach ($attributes as $attribute) {
						$player->$attribute = 0;
					}

					$player->training_points_spent	= 0;
					$player->save();

					break;

				case 429:
				case 1864:
					$character							= Character::find($_POST["character_id"]);

					$player->character_id				= $_POST["character_id"];
					$player->character_theme_id			= $character->themes()[0]->id;
					$player->character_theme_image_id	= $character->themes()[0]->images()[0]->id;
					$player->character_ability_id		= CharacterAbility::find_first('character_id=' . $player->character_id . ' AND is_initial=1', ['cache' => true])->id;
					$player->character_speciality_id	= CharacterSpeciality::find_first('character_id=' . $player->character_id . ' AND is_initial=1', ['cache' => true])->id;

					$player->save();
					
					$player_character_abilities = PlayerCharacterAbility::find("player_id=".$player->id);
					foreach($player_character_abilities as $player_character_ability){
						$player_character_ability->destroy();
					}
					//Adiciona as Habilidades do jogador
					$character_abilities = CharacterAbility::find("character_id=".$_POST["character_id"]);	
					foreach ($character_abilities as $character_ability){
						$player_character_ability = new PlayerCharacterAbility();
						$player_character_ability->player_id = $player->id;
						$player_character_ability->character_ability_id = $character_ability->id;
						$player_character_ability->character_id = $player->character_id;
						$player_character_ability->item_effect_ids = $character_ability->item_effect_ids;
						$player_character_ability->effect_chances = $character_ability->effect_chances;
						$player_character_ability->effect_duration = $character_ability->effect_duration;
						$player_character_ability->consume_mana = $character_ability->consume_mana;
						$player_character_ability->cooldown = $character_ability->cooldown;
						$player_character_ability->is_initial = $character_ability->is_initial;
						$player_character_ability->save();
						
					} 
					$player_character_specialities = PlayerCharacterSpeciality::find("player_id=".$player->id);
					foreach($player_character_specialities as $player_character_speciality){
						$player_character_speciality->destroy();
					}
					//Adiciona as Especialidades do jogador
					$character_specialities = CharacterSpeciality::find("character_id=".$_POST["character_id"]);	
					foreach ($character_specialities as $character_speciality){
						$player_character_speciality = new PlayerCharacterSpeciality();
						$player_character_speciality->player_id = $player->id;
						$player_character_speciality->character_speciality_id = $character_speciality->id;
						$player_character_speciality->character_id = $player->character_id;
						$player_character_speciality->item_effect_ids = $character_speciality->item_effect_ids;
						$player_character_speciality->effect_chances = $character_speciality->effect_chances;
						$player_character_speciality->effect_duration = $character_speciality->effect_duration;
						$player_character_speciality->consume_mana = $character_speciality->consume_mana;
						$player_character_speciality->cooldown = $character_speciality->cooldown;
						$player_character_speciality->is_initial = $character_speciality->is_initial;
						$player_character_speciality->save();
						
					} 

					break;

				case 430:
					$player->name	= $_POST["name"];
					$player->save();

					break;
					
				case 431:
					if(!$player->less_stamina==0){
						$stamina = percent(50,$player->for_stamina(true));
						$player->less_stamina	= ($player->less_stamina - $stamina) <= 0 ? 0 : $player->less_stamina - $stamina;
						$player->save();
					}
				break;
				case 432:
					$player->less_stamina	= 0;
					$player->save();
				break;
				case 1709:
					$user->character_slots += 1;
					$user->save();
				break;
				case 1715:
					if (!$player->has_item(1715)) {
						$player_item				= new PlayerItem();
						$player_item->item_id		= 1715;
						$player_item->player_id		= $player->id;
						$player_item->quantity		= 5;
						$player_item->save();
					}else{
						$player_item  = PlayerItem::find_first("player_id=". $player->id." and item_id=1715");
						$player_item->quantity	+= 5;
						$player_item->save();
						
					}
				break;
				case 1718:
					$player->currency	+= 2000;
					$player->save();
				break;
				case 1745:
					$player_organization->name	= $_POST["name_organization"];
					$player_organization->save();

				break;
				case 1746:
					$player->faction_id = $faction->id;
					$player->save();

				break;
				case 2112:
					$item_446 = PlayerItem::find_first("player_id =". $player->id. " AND item_id=446");
					
					if($item_446){
						$item_446->quantity += 100;
						$item_446->save();
					}else{
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

	function make_donation() {
		$is_dbl	= StarDouble::find_first('NOW() BETWEEN data_init AND data_end');
		if ($is_dbl) {
			$this->assign("is_dbl", $is_dbl);
		} else {
			$this->assign("is_dbl", FALSE);	
		}

		$methods	= [
			'pagseguro'		=> 'BRL',
			'paypal_eur'	=> 'EUR',
			'paypal_usd'	=> 'USD',
			// 'paypal_brl'	=> 'BRL'
		];
		$symbols	= [
			'BRL'			=> 'R$',
			'EUR'			=> '€',
			'USD'			=> '$',
		];

		$this->assign("methods",	$methods);
		$this->assign("symbols",	$symbols);
		$this->assign("plans",		StarPlan::all());
		$this->assign("player",		Player::get_instance());
	}
	function pay_donation(){
		$user = User::get_instance();
		
		$this->as_json			= TRUE;
		$this->json->success	= FALSE;
		$errors					= [];
		
		if(!isset($_POST['mode']) || (isset($_POST['mode']) && !is_numeric($_POST['mode']))) {
			$errors[]	= t('vips.errors.plan_invalid');
		}else{
			$star_plan = StarPlan::find_first("id=" . $_POST['mode']);
			if(!$star_plan){
				$errors[]	= t('vips.errors.plan_invalid');
			}
		}
		
		if (!sizeof($errors)) {
			//Adiciona o Plano Vip na tabela de aguarde do Usuário
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
	function done_donation() {
		$this->render	= false;
        $this->layout	= false;

		$player 		= Player::get_instance();
		$star_purchase	= StarPurchase::find_first("user_id=".$player->user_id." AND completed_at is null ORDER BY id DESC");
		
		if ($star_purchase) {
			$star_plan	= StarPlan::find_first("id = " . $star_purchase->star_plan_id);
			$coins		= [
				'pagseguro'		=> 'BRL',
				'paypal_eur'	=> 'EUR',
				'paypal_usd'	=> 'USD',
				'paypal_brl'	=> 'BRL'
			];
			$price		= 'price_' . strtolower($coins[$star_purchase->star_method]);

			switch ($star_purchase->star_method) {
				case 'pagseguro':
					\PagSeguro\Library::initialize();
					\PagSeguro\Library::cmsVersion()->setName(GAME_NAME)->setRelease(GAME_VERSION);
					\PagSeguro\Library::moduleVersion()->setName(GAME_NAME)->setRelease(GAME_VERSION);

					$payment = new \PagSeguro\Domains\Requests\Payment();
					$payment->addItems()->withParameters($star_plan->id, $star_plan->name, 1, $star_plan->$price);
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
					if (PAYPAL_SANDBOX) $p->useSandbox();
					$p->addField('business',		PAYPAL_EMAIL);
					$p->addField('return',			make_url('vips/make_donation?success'));
					$p->addField('cancel_return',	make_url('vips/make_donation?cancel'));
					$p->addField('notify_url',		make_url('callback/paypal'));
					$p->addField('item_name',		$star_plan->name);
					$p->addField('currency_code',	$coins[$star_purchase->star_method]);
					$p->addField('amount',			$star_plan->$price);
					$p->addField('custom',			$star_purchase->id);

					$p->submitPayment();
					break;
			}
		}
	}
}