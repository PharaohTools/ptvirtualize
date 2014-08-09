<?php

Namespace Info;

class HaltInfo extends CleopatraBase {

  public $hidden = false;

  public $name = "Halt - Stop a Phlagrant Box";

  public function __construct() {
    parent::__construct();
  }

  public function routesAvailable() {
    return array( "Halt" =>  array_merge( array("now") ) );
  }

  public function routeAliases() {
    return array("halt"=>"Halt");
  }

  public function helpDefinition() {
    $help = <<<"HELPDATA"
  This command allows you to halt a phlagrant box

  Halt, halt

        - now
        Halt a box now
        example: phlagrant halt now

HELPDATA;
    return $help ;
  }

}