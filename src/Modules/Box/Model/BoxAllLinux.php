<?php

Namespace Model;

class BoxAllLinux extends BaseTestInit {

    // Compatibility
    public $os = array("Linux") ;
    public $linuxType = array("Debian", "Redhat") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Initializer") ;
    private $paramsForBootstrappingModels ;

    public function __construct($params) {
        parent::__construct($params);
        $this->paramsForBootstrappingModels = $params ;
        $this->autopilotDefiner = "Box";
        $this->installCommands = array(
            "mkdir -p build/tests/box/",
            "cd build/tests/box/",
            "box --init" );
        $this->uninstallCommands = array(
            "rm -rf build/tests/box/" );
        $this->registeredPostInstallFunctions = array(
            "addTemplatesForFirstFeature",
            "addTemplatesForFirstFeatureContext" );
        $this->programNameMachine = "box"; // command and app dir name
        $this->programNameFriendly = " Box "; // 12 chars
        $this->programNameInstaller = "Box";
        $this->programExecutorTargetPath = 'box/bin/box';
        $this->initialize();
    }

}