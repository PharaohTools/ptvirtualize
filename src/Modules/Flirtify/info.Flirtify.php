<?php

Namespace Info;

class FlirtifyInfo extends Base {

    public $hidden = false;

    public $name = "Virtualize Flirtify - Generate a Virtufile";

    public function __construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "Flirtify" =>  array_merge(parent::routesAvailable(), array("default-ptconfigure", "default-ptconfigure-ptdeploy",
      "custom-ptconfigure-ptdeploy") ) );
    }

    public function routeAliases() {
      return array("flirt"=>"Flirtify", "flirtify"=>"Flirtify", "phlirt"=>"Flirtify", "phlirtify"=>"Flirtify");
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  With Flirtify you can create Virtualize files for your project from predefined templates. This will enable you to
  create default Virtual machine hardware and provisioning configurations for your project in a single command.

  Flirtify, flirt, flirtify, phlirt, phlirtify

        - default-ptconfigure
        Create a Virtufile for your project, with default Configuration Management for a PHP Application
        example: ptvirtualize flirt default-ptconfigure

        - default-ptconfigure-ptdeploy
        Create a Virtufile for your project, with default Configuration Management for a PHP Application and
        PTDeploy Application
        example: ptvirtualize flirt default-ptconfigure-ptdeploy
            --host-ptdeployfile=*relative/path/to/ptdeployfile/"
            # guess will use build/config/ptdeploy/ptdeployfy/autopilots/generated/

        - custom-ptconfigure-ptdeploy
        Create a Virtufile for your project
        example: ptvirtualize flirt custom-ptconfigure-ptdeploy

HELPDATA;
      return $help ;
    }

}