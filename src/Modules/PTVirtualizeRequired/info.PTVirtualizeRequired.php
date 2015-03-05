<?php

Namespace Info;

class PTVirtualizeRequiredInfo extends Base {

    public $hidden = true;

    public $name = "Virtualize Required Models";

    public function __construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "VirtualizeRequired" =>  array_merge(parent::routesAvailable() ) );
    }

    public function routeAliases() {
      return array();
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  This module provides no commands, but is required for Virtualize. It provides Models which are required for Virtualize.


HELPDATA;
      return $help ;
    }

}