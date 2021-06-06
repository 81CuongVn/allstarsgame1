<?php
class SiteNew extends Relation {
	function user() {
		return User::find($this->user_id);
	}

	function comments() {
		return SiteNewsComment::find('site_news_id=' . $this->id);
	}
}
