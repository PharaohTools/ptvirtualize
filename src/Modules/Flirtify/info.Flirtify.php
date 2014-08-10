<?php

Namespace Info;

class FlirtifyInfo extends Base {

    public $hidden = false;

    public $name = "Phlagrant Flirtify - Generate a Phalgrantfile";

    public function __construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "Flirtify" =>  array_merge(parent::routesAvailable(), array("now") ) );
    }

    public function routeAliases() {
      return array("flirt"=>"Flirtify", "flirtify"=>"Flirtify", "phlirt"=>"Flirtify", "phlirtify"=>"Flirtify");
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  With Flirtify you can create Phlagrant files for your project from predefined templates.

  Flirtify, flirt, flirtify, phlirt, phlirtify

        - now, create
        Create a Phlagrantfile for your project
        example: phlagrant flirt now

HELPDATA;
      return $help ;
    }

}