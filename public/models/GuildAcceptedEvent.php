<?php
  class GuildAcceptedEvent extends Relation {
    function guild_event() {
      return GuildEvent::find($this->guild_event_id);
    }
  }
