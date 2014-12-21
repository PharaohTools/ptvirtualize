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
  This command allows you to halt a phlagrant box. When a VM is running, you can use this to turn the machine off -
  graciously by default, or forcefully if need be.

  Halt, halt

        - now
        Execute a "soft" power off to your Phlagrant VM. First, try the soft power button, if that fails we then
        attempt to SSH in to the box and issue a shutdown from the command line
        example: phlagrant halt now
        example: phlagrant halt now --fail-hard # If the soft power of fails, perform a Hard Shutdown (by Power Switch)

        - hard
        Force power off to your Phlagrant VM (by Power Switch)
        example: phlagrant halt hard

        - pause, suspend
        Pause your running Phlagrant VM
        example: phlagrant halt pause
        example: phlagrant halt suspend

HELPDATA;
    return $help ;
  }

}