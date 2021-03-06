<?php
	class ItemEffect extends Relation {
		static	$always_cached	= true;
		public	$chance			= 100;
		public	$duration		= 0;

		function tooltip() {
		}
	}