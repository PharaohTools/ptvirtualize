<?php

Namespace Info;

class DestroyInfo extends CleopatraBase {

  public $hidden = false;

  public $name = "Destroy - Stop a Phlagrant Box";

  public function __construct() {
    parent::__construct();
  }

  public function routesAvailable() {
    return array( "Destroy" =>  array_merge( array("now", "hard", "pause") ) );
  }

  public function routeAliases() {
    return array("destroy"=>"Destroy");
  }

  public function helpDefinition() {
    $help = <<<"HELPDATA"
  This command allows you to destroy a phlagrant box

  Destroy, destroy

        - now
        Destroy a box. This will delete all of the hardware of your virtual machine, including any storage on it.
        If you have shared folders between guest and host, that data stays on the host. Your Virtual Machine must be
        in a stopped or aborted state to destroy it.
        example: phlagrant destroy now

HELPDATA;
    return $help ;
  }

}