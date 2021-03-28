<?php
  class OrganizationAcceptedEvent extends Relation {
    function organization_event() {
      return OrganizationEvent::find($this->organization_event_id);
    }
  }