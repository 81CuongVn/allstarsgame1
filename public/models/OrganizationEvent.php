<?php
  class OrganizationEvent extends Relation {
    function maps() {
      return OrganizationMap::find('organization_event_id=' . $this->id);
    }

    function initial_map() {
      return OrganizationMap::find('organization_event_id=' . $this->id . ' AND initial_organization_map_id != 0');
    }

    function npcs() {
      $npcs = [];

      foreach ($this->maps() as $map) {
        $npcs = array_merge($npcs, $map->npcs());
      }

      return $npcs;
    }

    function image($path_only = false) {
      $path = "/images/dungeon/" . $this->id . ".jpg";

      if ($path_only) {
        return $path;
      } else {
        return '<img src="' . asset_url($path) . '" />';
      }
    }

    function unlocked($organization_id, $event_id, $player_id) {
      return OrganizationAcceptedEvent::find_first('organization_id=' . $organization_id . ' AND organization_event_id = '.$event_id.' AND finished_at is NULL');
    }

    function reward() {
      return OrganizationEventReward::find($this->organization_event_reward_id);
    }
  }