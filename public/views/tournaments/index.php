<?php echo partial('shared/title', [
    'title' => 'tournaments.title',
    'place' => 'tournaments.title'
]); ?>
<?php echo partial('shared/info', array(
    'id'		=> 5,
    'title'		=> 'tournaments.info.title',
    'message'	=> t('tournaments.info.description2')
)); ?>
<div class="ev-ranking">
	<div style="clear:left; float: left" class="barra-secao barra-secao-<?=$player->character()->anime_id;?>">
	<table width="725" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td width="200">&nbsp;</td>
			<td width="60" align="center"><?=t('rankings.players.header.posicao');?></td>
			<td width="200" align="center"><?=t('rankings.players.header.nome');?></td>
			<td width="100" align="center"><?=t('rankings.players.header.score');?></td>
		</tr>
	</table>
	</div>
	<table width="725" border="0" cellpadding="0" cellspacing="0">
		<?php
		$counter = 0;
		foreach ($tournaments as $tournament) {
			$color	= $counter++ % 2 ? '091e30' : '173148';
        }
		?>
    </table>
</div>
<style>
    div.jQBracket .team div.label {
        color: black
    }
</style>
<script type="text/javascript">
    var doubleEliminationData = {
        teams : [
            ["Jogador 1", "Jogador 2"],
            ["Jogador 3", "Jogador 4"],
            ["Jogador 5", "Jogador 6"],
            ["Jogador 7", "Jogador 8"],
            ["Jogador 9", "Jogador 10"],
            ["Jogador 11", "Jogador 12"],
            ["Jogador 13", "Jogador 14"],
            ["Jogador 15", "Jogador 16"],
            ["Jogador 17", "Jogador 18"],
            ["Jogador 19", "Jogador 20"],
            ["Jogador 21", "Jogador 22"],
            ["Jogador 23", "Jogador 24"],
            ["Jogador 25", "Jogador 26"],
            ["Jogador 27", "Jogador 28"],
            ["Jogador 29", "Jogador 30"],
            ["Jogador 31", "Jogador 32"]
        ],
        results : [
            [ [1,0], [0,1], [1,0], [0,1], [1,0], [0,1], [1,0], [0,1] , [0,1], [1,0], [0,1], [1,0], [0,1], [1,0], [0,1], [1,0], [0,1] , [0,1] ],
            [ [1,0], [0,1], [1,0], [0,1], [1,0], [0,1], [1,0], [0,1] ],
            [ [1,0], [0,1], [1,0], [0,1] ],
            [ [1,0], [0,1] ],
            [ [1,0], [1,0] ]
        ]
    }
</script>
<div id="noSecondaryFinal">
    <h3>No secondary final</h3>

    <p>In double elimination, you can disable the secondary final which would
    generally be used if Loser Bracket winner wins the first match against
    Winner Bracket winner.</p>

    <script type="text/javascript">
        $(function() {
            $('div#noSecondaryFinal .demo').bracket({
                // skipSecondaryFinal: true,
                init: doubleEliminationData
            })
        })
    </script>
    <div class="demo"></div>
</div>