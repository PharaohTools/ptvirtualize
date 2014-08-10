<?php

Namespace Info;

class HaltInfo extends CleopatraBase {

  public $hidden = false;

  public $name = "Halt - Stop a Phlagrant Box";

  public function __construct() {
    parent::__construct();
  }

  public function routesAvailable() {
    return array( "Halt" =>  array_merge( array("now", "hard", "pause", "suspend") ) );
  }

  public function routeAliases() {
    return array("halt"=>"Halt");
  }

  public function helpDefinition() {
    $help = <<<"HELPDATA"
  This command allows you to halt a phlagrant box

  Halt, halt

        - now
        Execute a "soft" power off to your Phlagrant VM
        example: phlagrant halt now

        - hard
        Force power off to your Phlagrant VM
        example: phlagrant halt hard

        - pause, suspend
        Pause your running Phlagrant VM
        example: phlagrant halt pause
        example: phlagrant halt suspend

HELPDATA;
    return $help ;
  }

}