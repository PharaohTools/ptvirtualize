<?php

Namespace Info;

class ShellInfo extends CleopatraBase {

    public $hidden = false;

    public $name = "Pharaoh Tools Provisioner Integration";

    public function _construct() {
        parent::__construct();
    }

    public function routesAvailable() {
        return array( "Shell" => array_merge(parent::routesAvailable(), array("help") ) );
    }

    public function routeAliases() {
        return array("pharaoh-tools"=>"Shell");
    }

    public function provisonerName() {
        return "pharaoh-tools";
    }

    public function helpDefinition() {
       $help = <<<"HELPDATA"
    This extension provides integration with Shell as a Phlagrant Provisioner. It provides code
    functionality, but no extra commands.

    Pharaoh Tools

HELPDATA;
      return $help ;
    }

}