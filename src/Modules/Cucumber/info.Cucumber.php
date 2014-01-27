<?php

Namespace Info;

class CucumberInfo extends Base {

    public $hidden = false;

    public $name = "Cucumber - Initialize or Execute a Cucumber Test Suite";

    public function __construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "Cucumber" =>  array_merge(parent::routesAvailable(), array() ) );
    }

    public function routeAliases() {
      return array("cucumber"=>"Cucumber");
    }

    public function modelGroups() {
        return array("init"=>"Initializer","initialize"=>"Initializer");
    }

    public function autoPilotVariables() {
      return array(
        "Cucumber" => array(
          "Cucumber" => array(
            "programNameMachine" => "cucumber", // command and app dir name
          )
        )
      );
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  This command allows you to initialize a Cucumber test suite.

  Cucumber, cucumber

        - init, initialize
        Initialises the Cucumber test suite of this project
        example: testingkamen cucumber init
        example: testingkamen cucumber initialize

        - execute
        Executes the Cucumber test suite of this project
        example: testingkamen cucumber execute

HELPDATA;
      return $help ;
    }

}