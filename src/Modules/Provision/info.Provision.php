<?php

Namespace Info;

class ProvisionInfo extends PTConfigureBase {

  public $hidden = false;

  public $name = "Provision - Stop a Virtualize Box";

  public function __construct() {
    parent::__construct();
  }

  public function routesAvailable() {
    return array( "Provision" =>  array_merge( array("now", "hard", "pause") ) );
  }

  public function routeAliases() {
    return array("provision"=>"Provision");
  }

  public function helpDefinition() {
    $help = <<<"HELPDATA"
  This command allows you to provision a ptvirtualize box

  Provision, provision

        - now
        Provision a box now
        example: ptvirtualize provision now

HELPDATA;
    return $help ;
  }

}