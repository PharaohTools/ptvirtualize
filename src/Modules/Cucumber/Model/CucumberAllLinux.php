<?php

Namespace Model;

class CucumberAllLinux extends BaseTestInit {

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
        $this->autopilotDefiner = "Cucumber";
        $this->installCommands = array(
            "mkdir -p build/tests/cucumber/",
            "cd build/tests/cucumber/");
        $this->uninstallCommands = array(
            "rm -rf build/tests/cucumber/" );
        $this->registeredPostInstallFunctions = array(
            "addTemplatesForFirstFeature",
            "addTemplatesForStepDefinitions",
            "addTemplatesForSupport",
            "addTemplatesForGemfile" );
        $this->programNameMachine = "cucumber"; // command and app dir name
        $this->programNameFriendly = " Cucumber "; // 12 chars
        $this->programNameInstaller = "Cucumber";
        $this->programExecutorTargetPath = 'cucumber/bin/cucumber';
        $this->initialize();
    }

    protected function addTemplatesForFirstFeature() {
        $templatorFactory = new \Model\Templating();
        $templator = $templatorFactory->getModel($this->params);
        $originalTemplate = dirname(__FILE__)."/../Templates/features/first.feature" ;
        $appPath = $this->findPathOfApp() ;
        $targetLocation = $appPath."build/tests/cucumber/features/first.feature" ;
        $templator->template($originalTemplate, array(), $targetLocation );
    }

    protected function addTemplatesForStepDefinitions() {
        $templatorFactory = new \Model\Templating();
        $templator = $templatorFactory->getModel($this->params);
        $originalTemplate = dirname(__FILE__)."/../Templates/features/step_definitions/basic_steps.rb" ;
        $appPath = $this->findPathOfApp() ;
        $targetLocation = $appPath."build/tests/cucumber/features/step_definitions/basic_steps.rb" ;
        $templator->template($originalTemplate, array(), $targetLocation );
    }

    protected function addTemplatesForSupport() {
        $templatorFactory = new \Model\Templating();
        $templator = $templatorFactory->getModel($this->params);
        $appPath = $this->findPathOfApp() ;
        $appUrl = $this->findUrlOfApp() ;
        $originalTemplate = dirname(__FILE__)."/../Templates/features/support/archiver.rb" ;
        $targetLocation = $appPath."build/tests/cucumber/features/support/archiver.rb" ;
        $templator->template($originalTemplate, array(), $targetLocation );
        $originalTemplate = dirname(__FILE__)."/../Templates/features/support/env.rb" ;
        $targetLocation = $appPath."build/tests/cucumber/features/support/env.rb" ;
        $templator->template($originalTemplate, array("app_url" => $appUrl), $targetLocation );
        $originalTemplate = dirname(__FILE__)."/../Templates/features/support/hooks.rb" ;
        $targetLocation = $appPath."build/tests/cucumber/features/support/hooks.rb" ;
        $templator->template($originalTemplate, array(), $targetLocation );
    }

    protected function addTemplatesForGemfile() {
        $templatorFactory = new \Model\Templating();
        $templator = $templatorFactory->getModel($this->params);
        $originalTemplate = dirname(__FILE__)."/../Templates/Gemfile" ;
        $appPath = $this->findPathOfApp() ;
        $targetLocation = $appPath."build/tests/cucumber/Gemfile" ;
        $templator->template($originalTemplate, array(), $targetLocation );
    }

    protected function findUrlOfApp() {
        if (isset($this->params["cucumber-target-url"]) && $this->params["cucumber-target-url"] != "") {
            $urlOfApp = $this->params["cucumber-target-url"] ; }
//        else if (isset($this->params["cucumber-environment"]) && $this->params["cucumber-environment"] != "") {
//            $urlOfApp = $this->getUrlFromPapyrus($this->params["cucumber-environment"]) ; }
        return $urlOfApp = (isset($urlOfApp)) ? $urlOfApp : null  ;
    }

    protected function findPathOfApp() {
        if (isset($this->params["cucumber-target-path"]) && $this->params["cucumber-target-path"] != "") {
            $pathOfApp = $this->params["cucumber-target-path"] ; }
//        else if (isset($this->params["cucumber-environment"]) && $this->params["cucumber-environment"] != "") {
//            $pathOfApp = $this->getPathFromPapyrus($this->params["cucumber-environment"]) ; }
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