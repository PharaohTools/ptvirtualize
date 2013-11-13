<?php

Namespace Model;

class BehatAllLinuxMac extends BaseTestInit {

    // Compatibility
    public $os = array("Linux", "Darwin") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("any") ;

    public function __construct($params) {
        parent::__construct($params);
        $this->autopilotDefiner = "BehatInitializer";
        $this->initCommands = array(
           "echo shakalak"
        );
        $this->programNameFriendly = "Behat Test Suite Initializer! "; // 12 chars
        $this->initialize();
    }

}