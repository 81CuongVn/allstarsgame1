<?php echo partial('shared/title', array('title' => 'upgrade.title', 'place' => 'upgrade.title')) ?>

<table width="725" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="143" valign="top"><div id="equipment-list">
				<?php foreach ($equipments as $equipment): 
				?>
				<?php
						$item		= $equipment->item();
					?>
				<div class="equipment"> <img src="<?php echo image_url($item->image(true)) ?>" class="equipment-popover" data-id="<?=$equipment->id;?>" data-embed="<?php echo $item->embed() ?>" /> <?php echo $item->tooltip() ?> </div>
				<?php endforeach ?>
			</div></td>
		<td width="582" colspan="2"> Para você aprimorar seu Equipamento você precisará de itens especiais que possuem poderes diferentes:<br />
			<br />
			Com a Areia Estrelar você adicionará <span class="laranja">1 novo atributo aleatório</span> no seu equipamento. <br />
			<br />
			Com o Sangue de um Deus você adicionará <span class="laranja">2 novos atributos aleatórios</span> no seu equipamento.<br />
			<br />
			Com a Gema de Aprimoramento você destrói <span class="laranja">um equipamento e gera ele novamente em outra raridade</span>.<br />
		<br /></td>
	</tr>
</table>
<table width="725" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="25%" align="center"><div  style="position: relative;  left: 14px;  top: 53px;  background-color: #000;  width: 21px;  text-align: center;"><?php echo $item_1719 ? $item_1719->quantity : 0?></div>
			<img src="<?php echo image_url("items/areia.png") ?>" /><br />
			<a class="btn btn-sm btn-primary upgrade" data-id="<?php echo $equipment->id ?>" data-method="1719">Aprimorar:<br />
			1x Areia Estelar</a></td>
		<td width="25%" align="center"><div  style="position: relative;  left: 14px;  top: 53px;  background-color: #000;  width: 21px;  text-align: center;"><?php echo $item_1720 ? $item_1720->quantity : 0?></div>
			<img src="<?php echo image_url("items/sangue.png") ?>" /><br />
			<a class="btn btn-sm btn-primary upgrade" data-id="<?php echo $equipment->id ?>" data-method="1720">Aprimorar:<br />
			1x Sangue de Deus</a></td>
		<td width="25%" align="center">
			<div  style="position: relative;  left: 14px;  top: 53px;  background-color: #000;  width: 21px;  text-align: center;"><?php echo $item_1852 ? $item_1852->quantity : 0?></div>
			<img src="<?php echo image_url("items/gema_comum.png") ?>" /><br />
			<a class="btn btn-sm btn-primary upgrade" data-id="<?php echo $equipment->id ?>" data-method="1852">Aprimorar:<br />
			1x Gema Rara</a>
		</td>
		<td width="25%" align="center">
			<div  style="position: relative;  left: 14px;  top: 53px;  background-color: #000;  width: 21px;  text-align: center;"><?php echo $item_1853 ? $item_1853->quantity : 0?></div>
			<img src="<?php echo image_url("items/gema_comum.png") ?>" /><br />
			<a class="btn btn-sm btn-primary upgrade" data-id="<?php echo $equipment->id ?>" data-method="1853">Aprimorar:<br />
			1x Gema Lendária</a>
		</td>
	</tr>
</table>
