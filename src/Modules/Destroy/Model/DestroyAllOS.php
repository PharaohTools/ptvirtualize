<?php

Namespace Model;

class DestroyAllOS extends BaseFunctionModel {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default") ;

    public function __construct($params) {
        parent::__construct($params);
        $this->initialize();
    }

    public function destroyNow() {
        $this->loadFiles();
        $this->findProvider();
        if ($this->currentStateIsDestroyable() == false) { return ; }
        $this->runHook("pre") ;
        $this->removeShares();
        $this->doDestruction();
        $this->runHook("post") ;
        $this->deleteFromPapyrus() ;
    }

    protected function deleteFromPapyrus() {
        \Model\AppConfig::deleteProjectVariable($this->phlagrantfile->config["vm"]["name"], null, null, true) ;
    }

    protected function removeShares() {
        $upFactory = new \Model\Up();
        $modifyVM = $upFactory->getModel($this->params, "ModifyVM") ;
        $modifyVM->papyrus = $this->papyrus ;
        $modifyVM->phlagrantfile = $this->phlagrantfile ;
        $modifyVM->removeShares() ;
    }

    protected function doDestruction() {
        $this->provider->destroyVM($this->phlagrantfile->config["vm"]["name"]);
    }

    protected function currentStateIsDestroyable() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $destroyables = $this->provider->getDestroyableStates();
        if ($this->provider->isVMInStatus($this->phlagrantfile->config["vm"]["name"], $destroyables) == true) {
            $logging->log("This VM is in a Destroyable state...") ;
            return true ; }
        $logging->log("This VM is not in a Destroyable state...") ;
        return false ;
    }

    protected function runHook($type) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (isset($this->params["ignore-hooks"]) ) {
            $loggingFactory = new \Model\Logging();
            $logging = $loggingFactory->getModel($this->params) ;
            $logging->log("Not provisioning destroy hooks as ignore hooks parameter is set");
            return ; }
        $ut = ucfirst($type) ;
        $logging->log("Provisioning $ut Destroy Hooks");
        $provisionFactory = new \Model\Provision();
        $provision = $provisionFactory->getModel($this->params) ;
        $provision->provisionHook("destroy", $type);
    }

}