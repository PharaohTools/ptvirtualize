<?php

Namespace Model;

class BaseFunctionModel extends BaseLinuxApp {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default") ;

    public $virtufile;
    public $papyrus ;
    public $provider ;

    public function __construct($params) {
        parent::__construct($params);
        $this->initialize();
    }

    protected function loadFiles() {
        $this->virtufile = $this->loadVirtufile();
        if ($this->virtufile==false) { return false ; }
        $this->papyrus = $this->loadPapyrusLocal();
        return true ;
    }

    protected function loadVirtufile() {
        $prFactory = new \Model\PTVirtualizeRequired();
        $ptvirtualizeFileLoader = $prFactory->getModel($this->params, "VirtufileLoader") ;
        return $ptvirtualizeFileLoader->load() ;
    }

    protected function loadPapyrusLocal() {
        $prFactory = new \Model\PTVirtualizeRequired();
        $papyrusLocalLoader = $prFactory->getModel($this->params, "PapyrusLocalLoader") ;
        return $papyrusLocalLoader->load($this->virtufile) ;
    }

    protected function findProvider($modGroup = "BoxDestroy") {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (isset($this->virtufile->config["vm"]["provider"])) {
            $logging->log("Provider {$this->virtufile->config["vm"]["provider"]} found in Virtufile", $this->getModuleName()) ;
            $this->provider = $this->getProvider($this->virtufile->config["vm"]["provider"], $modGroup) ; }
        else {
            $logging->log("No Provider configured in Virtufile.", $this->getModuleName(), LOG_FAILURE_EXIT_CODE); }
    }

    protected function getProvider($provider, $modGroup) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $infoObjects = \Core\AutoLoader::getInfoObjects();
        $allProviders = array();
        foreach($infoObjects as $infoObject) {
            if ( method_exists($infoObject, "vmProviderName") ) {
                $allProviders[] = $infoObject->vmProviderName(); } }
        foreach($allProviders as $oneProvider) {
            if ( $provider == $oneProvider ) {
                $className = '\Model\\'.ucfirst($oneProvider) ;
                $providerFactory = new $className();
                $provider = $providerFactory->getModel($this->params, $modGroup);
                if (is_object($provider)) { return $provider ; }
                else {
                    \Core\BootStrap::setExitCode(1);
                    $logging->log("No Model in Group $modGroup available for provider $oneProvider", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
                    break ; } } }
        return false ;
    }


    protected function runHook($hook, $type) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (isset($this->params["ignore-hooks"]) ) {
            $logging->log("Not provisioning $hook $type hooks as ignore hooks parameter is set", $this->getModuleName());
            return true; }
        $ut = ucfirst($type) ;
        $logging->log("Provisioning $hook $ut hooks", $this->getModuleName());
        $provisionFactory = new \Model\Provision();
        $provision = $provisionFactory->getModel($this->params) ;
        $res = $provision->provisionHook($hook, $type);
        if ($res == false) {
            $logging->log("Provisioning $hook $ut hooks failed", $this->getModuleName(), LOG_FAILURE_EXIT_CODE); }
        return $res ;
    }

}