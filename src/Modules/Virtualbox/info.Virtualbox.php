<?php

Namespace Info;

class VirtualboxInfo extends CleopatraBase {

    public $hidden = false;

    public $name = "Virtualbox Provider Integration";

    public function _construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "Virtualbox" => array_merge(parent::routesAvailable(), array("help") ) );
    }

    public function routeAliases() {
      return array("virtualbox"=>"Virtualbox");
    }

    public function vmProviderName() {
        return "virtualbox";
    }

    public function helpDefinition() {
       $help = <<<"HELPDATA"
    This extension provides integration with Virtualbox as a VM provider. It provides code
    functionality, but no extra commands.

    Virtualbox

HELPDATA;
      return $help ;
    }

}