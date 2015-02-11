<?php

Namespace Info;

class StatusInfo extends CleopatraBase {

  public $hidden = false;

  public $name = "Status - Stop a Virtualizer Box";

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
  This command allows you to status a virtualizer box

  Status, status

        - show
        Show execution status information of your Virtualizer VM
        example: virtualizer status show

        - full
        Show full status information of your Virtualizer VM
        example: virtualizer status full

HELPDATA;
    return $help ;
  }

}