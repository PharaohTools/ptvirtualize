<?php

Namespace Info;

class UpInfo extends CleopatraBase {

  public $hidden = false;

  public $name = "Up - Create and Start a Virtualize Box";

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
  This command allows you to create, start and provision virtualize boxes.

  Up, up

        - now
        Bring up a box now
        example: virtualize up now
        example: virtualize up now --modify # modify the hardware settings to match the Virtualizefile during the up phase.
            Without it, the machine will be brought up with its previous settings. On creating new machines this will
            happen automatically regardless of the parameter.
        example: virtualize up now --provision # provision an existing machine with the configuration scripts specified
            in the Virtualizefile. Without it, the machine will be brought up with its previous config. On creating
            new machines this will happen automatically regardless of the parameter.
        example: virtualize up now --modify --provision # modify and provision an existing box during the up phase

HELPDATA;
    return $help ;
  }

}