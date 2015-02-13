<?php

Namespace Info;

class ResumeInfo extends PTConfigureBase {

  public $hidden = false;

  public $name = "Resume - Stop a Virtualize Box";

  public function __construct() {
    parent::__construct();
  }

  public function routesAvailable() {
    return array( "Resume" =>  array_merge( array("now") ) );
  }

  public function routeAliases() {
    return array("resume"=>"Resume");
  }

  public function helpDefinition() {
    $help = <<<"HELPDATA"
  This command allows you to resume a ptvirtualize box, which has been paused/suspended.
  This will start the box and will not attempt to modify or provision it.

  Resume, resume

        - now
        Resume a box now
        example: ptvirtualize resume now

HELPDATA;
    return $help ;
  }

}