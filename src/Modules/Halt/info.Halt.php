<?php

Namespace Info;

class HaltInfo extends CleopatraBase {

  public $hidden = false;

  public $name = "Halt - Stop a Virtualizer Box";

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
  This command allows you to halt a virtualizer box. When a VM is running, you can use this to turn the machine off -
  graciously by default, or forcefully if need be.

  Halt, halt

        - now
        Execute a "soft" power off to your Virtualizer VM. First, try the soft power button, if that fails we then
        attempt to SSH in to the box and issue a shutdown from the command line
        example: virtualizer halt now
        example: virtualizer halt now --fail-hard # If the soft power of fails, perform a Hard Shutdown (by Power Switch)

        - hard
        Force power off to your Virtualizer VM (by Power Switch)
        example: virtualizer halt hard

        - pause, suspend
        Pause your running Virtualizer VM
        example: virtualizer halt pause
        example: virtualizer halt suspend

HELPDATA;
    return $help ;
  }

}