<?php

Namespace Info;

class BehatInfo extends Base {

    public $hidden = false;

    public $name = "Behat - Initilase or Execute a Behat Test Suite";

    public function __construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "Behat" =>  array_merge(parent::routesAvailable(), array("install") ) );
    }

    public function routeAliases() {
      return array("cleo"=>"Behat", "cleopatra"=>"Behat");
    }

    public function autoPilotVariables() {
      return array(
        "Behat" => array(
          "Behat" => array(
            "programNameMachine" => "cleopatra", // command and app dir name
            "programNameFriendly" => " Behat! ",
            "programNameInstaller" => "Behat - Update to latest version",
            "programExecutorTargetPath" => 'cleopatra/src/Bootstrap.php',
          )
        )
      );
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  This command allows you to update Behat.

  Behat, cleo, cleopatra

        - install
        Installs the latest version of cleopatra
        example: cleopatra cleopatra install

HELPDATA;
      return $help ;
    }

}