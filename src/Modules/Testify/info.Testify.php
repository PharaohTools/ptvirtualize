<?php

Namespace Info;

class TestifyInfo extends Base {

    public $hidden = false;

    public $name = "Cleopatra Testifyer - Creates default tests for your project";

    public function __construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "Testify" =>  array_merge(parent::routesAvailable(), array("standard") ) );
    }

    public function routeAliases() {
      return array("testify"=>"Testify");
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  This command is part of a default Module Core and provides you with a method by which you can
  create a standard set of Autopilot files for your project from the command line.


  Testify, testify

        - list
        List all of the autopilot files in your build/tests/cleopatra/autopilots
        example: cleopatra testify list

        - standard
        Create a default set of tests in build/tests/cleopatra/autopilots for
        your project.
        example: cleopatra testify create

HELPDATA;
      return $help ;
    }

}