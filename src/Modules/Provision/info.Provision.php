<?php

Namespace Info;

class ProvisionInfo extends CleopatraBase {

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
  This command allows you to provision a virtualize box

  Provision, provision

        - now
        Provision a box now
        example: virtualize provision now

HELPDATA;
    return $help ;
  }

}