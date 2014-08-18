<?php

Namespace Info;

class ShellInfo extends CleopatraBase {

    public $hidden = false;

    public $name = "Shell Provisioner Integration";

    public function _construct() {
        parent::__construct();
    }

    public function routesAvailable() {
        return array( "Shell" => array_merge(parent::routesAvailable(), array("help") ) );
    }

    public function routeAliases() {
        return array("shell"=>"Shell");
    }

    public function provisonerName() {
        return "shell";
    }

    public function helpDefinition() {
       $help = <<<"HELPDATA"
    This extension provides integration with Shell as a Phlagrant Provisioner. It provides code
    functionality, but no extra commands.

    Shell

HELPDATA;
      return $help ;
    }

}