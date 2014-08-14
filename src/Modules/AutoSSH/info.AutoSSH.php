<?php

Namespace Info;

class AutoSSHInfo extends CleopatraBase {

  public $hidden = false;

  public $name = "AutoSSH - Stop a Phlagrant Box";

  public function __construct() {
    parent::__construct();
  }

  public function routesAvailable() {
    return array( "AutoSSH" =>   array("show", "full", "help") );
  }

  public function routeAliases() {
    return array("status"=>"AutoSSH");
  }

  public function helpDefinition() {
    $help = <<<"HELPDATA"
  This command allows you to status a phlagrant box

  AutoSSH, status

        - show
        Show execution status information of your Phlagrant VM
        example: phlagrant status show

        - full
        Show full status information of your Phlagrant VM
        example: phlagrant status full

HELPDATA;
    return $help ;
  }

}