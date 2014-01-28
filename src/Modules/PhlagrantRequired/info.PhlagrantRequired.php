<?php

Namespace Info;

class PhlagrantRequiredInfo extends Base {

    public $hidden = true;

    public $name = "Phlagrant Required Models";

    public function __construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "PhlagrantRequired" =>  array_merge(parent::routesAvailable() ) );
    }

    public function routeAliases() {
      return array();
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  This module provides no commands, but is required for Phlagrant. It provides Models which are required for Phlagrant.


HELPDATA;
      return $help ;
    }

}