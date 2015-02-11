<?php

Namespace Info;

class PharaohToolsInfo extends CleopatraBase {

    public $hidden = false;

    public $name = "Pharaoh Tools Provisioner Integration";

    public function _construct() {
        parent::__construct();
    }

    public function routesAvailable() {
        return array( "PharaohTools" => array_merge(parent::routesAvailable(), array("help") ) );
    }

    public function routeAliases() {
        return array("pharaoh-tools"=>"PharaohTools");
    }

    public function provisonerName() {
        return "pharaoh-tools";
    }

    public function helpDefinition() {
       $help = <<<"HELPDATA"
    This extension provides integration with PharaohTools as a Virtualizer Provisioner. It provides code
    functionality, but no extra commands.

    Pharaoh Tools

HELPDATA;
      return $help ;
    }

}