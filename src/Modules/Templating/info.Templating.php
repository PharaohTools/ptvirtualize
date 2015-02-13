<?php

Namespace Info;

class TemplatingInfo extends Base {

    public $hidden = true;

    public $name = "Templating";

    public function __construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "Templating" =>  array_merge(parent::routesAvailable(), array("install") ) );
    }

    public function routeAliases() {
      return array("templating"=>"Templating", "template"=>"Templating");
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  This command allows you to apply a templated file within the file system.


  Templating, templating, template

        - install
        Installs a template
        example: ptvirtualize template install

HELPDATA;
      return $help ;
    }

}