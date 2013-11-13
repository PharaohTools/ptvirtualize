<?php

Namespace Info;

class BehatInfo extends Base {

    public $hidden = false;

    public $name = "Behat - Initialise or Execute a Behat Test Suite";

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
          )
        )
      );
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  This command allows you to initialise Behat testing into your app.

  Behat, behat

        - initialise
        initialises your behat files
        example: testingkamen behat initialise

HELPDATA;
      return $help ;
    }

}