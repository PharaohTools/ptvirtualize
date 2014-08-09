<?php

Namespace Info;

class UpInfo extends CleopatraBase {

  public $hidden = false;

  public $name = "Up - Create and Start a Phlagrant Box";

  public function __construct() {
    parent::__construct();
  }

  public function routesAvailable() {
    return array( "Up" =>  array_merge( array("now") ) );
  }

  public function routeAliases() {
    return array("up"=>"Up");
  }

  public function helpDefinition() {
    $help = <<<"HELPDATA"
  This command allows you to create, start and provision phlagrant boxes

  Up, up

        - now
        Bring up a box now
        example: phlagrant up now --provision

HELPDATA;
    return $help ;
  }

}