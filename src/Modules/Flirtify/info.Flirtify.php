<?php

Namespace Info;

class FlirtifyInfo extends Base {

    public $hidden = false;

    public $name = "Virtualize Flirtify - Generate a Phalgrantfile";

    public function __construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "Flirtify" =>  array_merge(parent::routesAvailable(), array("default-cleo", "default-cleo-dapper",
      "custom-cleo-dapper") ) );
    }

    public function routeAliases() {
      return array("flirt"=>"Flirtify", "flirtify"=>"Flirtify", "phlirt"=>"Flirtify", "phlirtify"=>"Flirtify");
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  With Flirtify you can create Virtualize files for your project from predefined templates. This will enable you to
  create default Virtual machine hardware and provisioning configurations for your project in a single command.

  Flirtify, flirt, flirtify, phlirt, phlirtify

        - default-cleo
        Create a Virtufile for your project, with default Configuration Management for a PHP Application
        example: virtualize flirt default-cleo

        - default-cleo-dapper
        Create a Virtufile for your project, with default Configuration Management for a PHP Application and
        Dapperstrano Application
        example: virtualize flirt default-cleo-dapper
            --host-dapperfile=*relative/path/to/dapperfile/"
            # guess will use build/config/dapperstrano/dapperfy/autopilots/generated/

        - custom-cleo-dapper
        Create a Virtufile for your project
        example: virtualize flirt custom-cleo-dapper

HELPDATA;
      return $help ;
    }

}