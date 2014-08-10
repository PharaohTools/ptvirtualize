<?php

Namespace Info;

class ProvisionInfo extends CleopatraBase {

  public $hidden = false;

  public $name = "Provision - Stop a Phlagrant Box";

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
  This command allows you to provision a phlagrant box

  Provision, provision

        - now
        Provision a box now
        example: phlagrant provision now

HELPDATA;
    return $help ;
  }

}