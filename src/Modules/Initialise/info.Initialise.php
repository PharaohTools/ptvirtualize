<?php

Namespace Info;

class InitialiseInfo extends Base {

    public $hidden = false;

    public $name = "Virtualize Initialise - Generate a Virtufile";

    public function __construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "Initialise" =>  array_merge(parent::routesAvailable(), array(
          'now', 'file', 'virtufile') ) );
    }

    public function routeAliases() {
      return array(
            "init"=>"Initialise", "initialise"=>"Initialise"
      );
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  With Initialise you can create Virtualize files for your project from predefined templates. This will enable you to
  create default Virtual machine hardware and provisioning configurations for your project in a single command.

  Initialise, initialise, init

        - now
        Create a Virtufile for your project
        example: ptvirtualize init now

HELPDATA;
      return $help ;
    }

}