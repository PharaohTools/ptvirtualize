<?php

Namespace Model;

class BehatAllLinux extends BaseTestInit {

    // Compatibility
    public $os = array("Linux") ;
    public $linuxType = array("Debian", "Redhat") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Initializer") ;

    public function __construct($params) {
        parent::__construct($params);
        $this->autopilotDefiner = "Behat";
        $this->installCommands = array(
            "mkdir -p build/tests/behat/",
            "cd build/tests/behat/",
            "behat --init" );
        $this->uninstallCommands = array(
            "sudo rm -rf build/tests/behat/" );
        $this->programNameMachine = "behat"; // command and app dir name
        $this->programNameFriendly = " Behat "; // 12 chars
        $this->programNameInstaller = "Behat";
        $this->programExecutorTargetPath = 'behat/bin/behat';
        $this->initialize();
    }

}