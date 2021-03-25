<?php echo partial('shared/title', [
    'title' => 'tournaments.title',
    'place' => 'tournaments.title'
]); ?>
<?php echo partial('shared/info', array(
    'id'		=> 5,
    'title'		=> 'tournaments.info.title',
    'message'	=> t('tournaments.info.description', [
        'currency'  => t('currencies.' . $player->character()->anime_id)
    ])
)); ?><br />
<div class="ev-ranking">
	<div style="clear:left; float: left" class="barra-secao barra-secao-<?=$player->character()->anime_id;?>">
	<table width="725">
		<tr>
			<td width="225">&nbsp;</td>
            <td width="100" class="text-center">Fase</td>
			<td width="100" class="text-center">Participantes</td>
            <td width="100" class="text-center">Inicio</td>
            <td width="100" class="text-center">Vencedor</td>
			<td width="100" class="text-center">Ações</td>
		</tr>
	</table>
	</div>
	<table width="700" class="table table-striped table-hover">
		<?php
		$counter = 0;
		foreach ($tournaments as $tournament) {
			$color	= $counter++ % 2 ? '091e30' : '173148';
        ?>
        <tr style="background-color: #<?=$color;?>;">
            <td width="225" class="text-center">
                <span style="font-size: 16px" class="amarelo">
                    <?php echo $tournament->name; ?>
                </span>
            </td>
            <td width="100" class="text-center">
                <?php
                if ($tournament->finished) {
                    echo '<span class="label label-success">' . t('tournaments.status.finished') . '</span>';
                } elseif ($tournament->canceled) {
                    echo '<span class="label label-danger">' . t('tournaments.status.canceled') . '</span>';
                } else {
                    echo '<span class="label label-default">' . $tournament->getRound() . '</span>';
                }
                ?>
            </td>
            <td width="100" class="text-center">
                <span class="label label-primary">
                    <?php echo sizeof($tournament->players()); ?> / <?php echo $tournament->places; ?>
                </span>
            </td>
            <td width="100" class="text-center">
                <?php echo date('d/m/Y H:i:s', strtotime($tournament->starts_at)); ?>
            </td>
            <td width="100" class="text-center">
                <?php
                if ($tournament->finished) {
                    echo '<span class="laranja">' . $tournament->winner()->player()->name . '</span>';
                } else {
                    echo '-';
                }
                ?>
            </td>
            <td width="100" class="text-center">
                <a href="<?php echo make_url('tournaments#show/' . $tournament->id); ?>" class="btn btn-sm btn-primary">Detalhes</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>