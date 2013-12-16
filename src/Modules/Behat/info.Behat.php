<?php

Namespace Info;

class BehatInfo extends Base {

    public $hidden = false;

    public $name = "Behat - Initialize or Execute a Behat Test Suite";

    public function __construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "Behat" =>  array_merge(parent::routesAvailable(), array() ) );
    }

    public function routeAliases() {
      return array("behat"=>"Behat");
    }

    public function autoPilotVariables() {
      return array(
        "Behat" => array(
          "Behat" => array(
            "programNameMachine" => "behat", // command and app dir name
            "programNameFriendly" => " Behat! ",
            "programNameInstaller" => "Behat - Update to latest version",
            "programExecutorTargetPath" => 'cleopatra/src/Bootstrap.php',
          )
        )
      );
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  This command allows you to initialize a Behat test suite.

  Behat, behat

        - init, initialize
        Initialises the Behat test suite of this project
        example: testingkamen behat init
        example: testingkamen behat initialise

        - execute
        Executes the Behat test suite of this project
        example: testingkamen behat execute

HELPDATA;
      return $help ;
    }

}