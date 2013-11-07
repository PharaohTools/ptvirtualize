<?php

Namespace Info;

class TestingkamenRequiredInfo extends Base {

    public $hidden = true;

    public $name = "Testingkamen Required Models";

    public function __construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "TestingkamenRequired" =>  array_merge(parent::routesAvailable() ) );
    }

    public function routeAliases() {
      return array();
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  This module provides no commands, but is required for Testingkamen. It provides Models which are required for Testingkamen.


HELPDATA;
      return $help ;
    }

}