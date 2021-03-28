<?php
  class OrganizationMap extends Relation {
    function objects() {
      return OrganizationMapObject::find('organization_map_id=' . $this->id);
    }

    function npcs() {
      return OrganizationMapObject::find('kind IN("npc", "sharednpc") AND organization_map_id=' . $this->id);
    }

    function at($x, $y) {
      return OrganizationMapObject::find_first("xpos={$x} AND ypos={$y} AND organization_map_id=" . $this->id);
    }
  }