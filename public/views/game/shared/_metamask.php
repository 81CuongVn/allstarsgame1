<?php if (!$user->wallet ) { ?>
	<button type="button" class="btn btn-primary btn-block mb-2" onclick="loginMetamask();">
		Conectar Metamask
	</button>
<?php } else { ?>
	<button type="button" class="btn btn-success btn-block mb-1">
		Metamask Conectada!
	</button>
	<div class="bg_menu_esquerdo">
		<div class="menu_esquerdo_divisao">
			<b class="amarelo">AASG</b>
			<b class="" id="tokenBalance">0</b>
		</div>
		<div class="menu_esquerdo_divisao">
			<b class="amarelo">BNB</b>
			<b class="" id="bnbBalance">0</b>
		</div>
	</div>
<?php } ?>
