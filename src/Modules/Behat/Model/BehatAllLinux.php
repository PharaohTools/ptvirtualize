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
    private $paramsForBootstrappingModels ;

    public function __construct($params) {
        parent::__construct($params);
        $this->paramsForBootstrappingModels = $params ;
        $this->autopilotDefiner = "Behat";
        $this->installCommands = array(
            "mkdir -p build/tests/behat/",
            "cd build/tests/behat/",
            "behat --init" );
        $this->uninstallCommands = array(
            "rm -rf build/tests/behat/" );
        $this->registeredPostInstallFunctions = array(
            "addTemplatesForFirstFeature",
            "addTemplatesForFirstFeatureContext" );
        $this->programNameMachine = "behat"; // command and app dir name
        $this->programNameFriendly = " Behat "; // 12 chars
        $this->programNameInstaller = "Behat";
        $this->programExecutorTargetPath = 'behat/bin/behat';
        $this->initialize();
    }

    protected function addTemplatesForFirstFeature() {
        $templatorFactory = new \Model\Templating();
        $templator = $templatorFactory->getModel($this->params);
        $originalTemplate = dirname(__FILE__)."/../Templates/first-feature.tpl.php" ;

        $appPath = $this->findPathOfApp() ;
        $targetLocation = $appPath."build/tests/behat/features/first.feature" ;

        $templator->template(
            $originalTemplate,
            array(),
            $targetLocation );

    }

    protected function addTemplatesForFirstFeatureContext() {
        $templatorFactory = new \Model\Templating();
        $templator = $templatorFactory->getModel($this->params);
        $originalTemplate = dirname(__FILE__)."/../Templates/FeatureContext.php" ;

        $appPath = $this->findPathOfApp() ;
        $targetLocation = $appPath."build/tests/behat/features/bootstrap/FeatureContext.php" ;
        $appUrl = $this->findUrlOfApp() ;

        $replacements = array( 'site_url' => $appUrl ) ;

        $templator->template(
            $originalTemplate,
            $replacements,
            $targetLocation );

    }

    protected function findUrlOfApp() {
        if (isset($this->params["behat-target-url"]) && $this->params["behat-target-url"] != "") {
            $urlOfApp = $this->params["behat-target-url"] ; }
//        else if (isset($this->params["behat-environment"]) && $this->params["behat-environment"] != "") {
//            $urlOfApp = $this->getUrlFromPapyrus($this->params["behat-environment"]) ; }
        return $urlOfApp = (isset($urlOfApp)) ? $urlOfApp : null  ;
    }

    protected function findPathOfApp() {
        if (isset($this->params["behat-target-path"]) && $this->params["behat-target-path"] != "") {
            $pathOfApp = $this->params["behat-target-path"] ; }
//        else if (isset($this->params["behat-environment"]) && $this->params["behat-environment"] != "") {
//            $pathOfApp = $this->getPathFromPapyrus($this->params["behat-environment"]) ; }
        if (isset($pathOfApp)) {
            echo $pathOfApp."\n" ;
            echo substr($pathOfApp, -1, 1)."\n" ;
            if (substr($pathOfApp, -1, 1) != '/') { $pathOfApp .= '/' ; } }
        return (isset($pathOfApp)) ? $pathOfApp : null  ;
    }

//    protected function getUrlFromPapyrus($environment) {
//        return "www.papyrus-url.co.uk" ;
//    }
//
//    protected function getPathFromPapyrus($environment) {
//        return "/var/www/papyrus-output/" ;
//    }

}