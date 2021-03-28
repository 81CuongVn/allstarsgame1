<script type="text/javascript" src="<?php echo asset_url('js/organization_dungeon.js') ?>"></script>
<?php echo partial('shared/title', array('title' => 'battles.npc.title', 'place' => 'battles.npc.title')) ?>
<style type="text/css">
	#dungeon-map-container {
		position: relative;
		overflow: hidden;
	}

	#dungeon-map-container .block {
		background-color: rgba(0, 0, 0, .1);
		border: solid 1px rgba(0, 0, 0, .1);
		position: absolute;
		z-index: 4;
	}

	#dungeon-map-container .block.hover {
		background-color: rgba(0, 0, 255, .3);
	}

	#dungeon-map-container .block.self {
		background-color: rgba(0, 255, 0, .3);
	}

	#dungeon-map-container .block.player {
		background-color: rgba(50, 255, 0, .3);
	}

	#dungeon-map-container .block.npc {
		background-color: rgba(255, 255, 0, .3);
	}

	#dungeon-map-container .block.sharednpc {
		background-color: rgba(0, 255, 255, .3);
	}

	#dungeon-map-container .block.chest, #dungeon-map-container .block.sharedchest {
		background-color: rgba(100, 255, 255, .3);
	}

	#dungeon-map-container .block.door {
		background-color: rgba(150, 255, 255, .3);
	}

	#dungeon-map-container .initial-dark {
		position: absolute;
		width: 100%;
		height: 100%;
		top: 0px;
		left: 0px;
	}

	#dungeon-map-container .dark {
		background-color: rgba(0, 0, 0, .39);
		z-index: 2;
		position: absolute;
	}

	#dungeon-map-container .fill-tl { top: 0px; left: 0px; }
	#dungeon-map-container .fill-tr { top: 0px; right: 0px; }
	#dungeon-map-container .fill-bl { bottom: 0px; left: 0px; }
	#dungeon-map-container .fill-br { bottom: 0px; right: 0px; }

	#dungeon-map-container .light {
		border-radius: 50%;
		border-width: 50px;
		z-index: 3;
	}

	#dungeon-map-container .icon-player, #dungeon-map-container .icon-npc, #dungeon-map-container .icon-sharednpc, #dungeon-map-container .icon-door, #dungeon-map-container .icon-chest, #dungeon-map-container .icon-sharedchest {
		position: absolute;
		z-index: 5;
		margin-left: 7px;
		margin-top: 6px;
	}
</style>
<div id="dungeon-map-container" style="background-image: url(<?php echo image_url("maps/dungeon/1/" . $map->id . ".jpg" ) ?>); width: 730px; height: 730px; left: 7px;" data-key="<?php echo $_SESSION['organization_dungeon_key'] ?>" data-player="<?php echo $player->id ?>">
	<div class="dark initial-dark"></div>
</div>
