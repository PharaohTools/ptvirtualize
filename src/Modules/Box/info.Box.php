<?php

Namespace Info;

class BoxInfo extends Base {

    public $hidden = false;

    public $name = "Box - Manage Base Boxes for Phlagrant";

    public function __construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "Box" =>  array_merge(parent::routesAvailable(), array() ) );
    }

    public function routeAliases() {
      return array("box"=>"Box");
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  This command allows you to manage the Base boxes available to you in Phlagrant

  Box, box

        - add
        Initialises the Box test suite of this project
        example: phlagrant box init
        example: phlagrant box initialize

        - remove
        Executes the Box test suite of this project
        example: phlagrant box execute

        - list
        Executes the Box test suite of this project
        example: phlagrant box execute

HELPDATA;
      return $help ;
    }

}