<?php
class GuildLevelReward extends Relation {
	function reward($level) {
		return GuildLevelReward::find_first('id = ' . $level);
	}
}
