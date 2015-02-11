<?php

Namespace Info;

class VirtualizerRequiredInfo extends Base {

    public $hidden = true;

    public $name = "Virtualizer Required Models";

    public function __construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "VirtualizerRequired" =>  array_merge(parent::routesAvailable() ) );
    }

    public function routeAliases() {
      return array();
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  This module provides no commands, but is required for Virtualizer. It provides Models which are required for Virtualizer.


HELPDATA;
      return $help ;
    }

}