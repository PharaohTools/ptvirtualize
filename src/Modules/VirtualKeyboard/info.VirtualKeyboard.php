<?php

Namespace Info;

class VirtualKeyboardInfo extends PTConfigureBase {

    public $hidden = false;

    public $name = "VirtualKeyboard Provisioner Integration";

    public function _construct() {
        parent::__construct();
    }

    public function routesAvailable() {
        return array( "VirtualKeyboard" => array_merge(parent::routesAvailable(), array("help") ) );
    }

    public function routeAliases() {
        return array("virtualKeyboard"=>"VirtualKeyboard");
    }

    public function provisonerName() {
        return "virtualKeyboard";
    }

    public function helpDefinition() {
       $help = <<<"HELPDATA"
    This extension provides integration with the Virtual Keyboard and Mouse of the Virtual Machine as a Virtualize
    Provisioner. It provides code functionality, but no extra commands.

    VirtualKeyboard

HELPDATA;
      return $help ;
    }

}