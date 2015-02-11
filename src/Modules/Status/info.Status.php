<?php

Namespace Info;

class StatusInfo extends CleopatraBase {

  public $hidden = false;

  public $name = "Status - Stop a Virtualize Box";

  public function __construct() {
    parent::__construct();
  }

  public function routesAvailable() {
    return array( "Status" =>   array("show", "full", "help") );
  }

  public function routeAliases() {
    return array("status"=>"Status");
  }

  public function helpDefinition() {
    $help = <<<"HELPDATA"
  This command allows you to status a virtualize box

  Status, status

        - show
        Show execution status information of your Virtualize VM
        example: virtualize status show

        - full
        Show full status information of your Virtualize VM
        example: virtualize status full

HELPDATA;
    return $help ;
  }

}