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
        $this->runHook("destroy", "pre") ;
        $this->removeShares();
        $this->doDestruction();
        $this->runHook("destroy", "post") ;
        $this->deleteFromPapyrus() ;
    }

    protected function deleteFromPapyrus() {
        \Model\AppConfig::deleteProjectVariable($this->virtufile->config["vm"]["name"], null, null, true) ;
    }

    protected function removeShares() {
        $upFactory = new \Model\Up();
        $modifyVM = $upFactory->getModel($this->params, "ModifyVM") ;
        $modifyVM->papyrus = $this->papyrus ;
        $modifyVM->virtufile = $this->virtufile ;
        $modifyVM->removeShares() ;
    }

    protected function doDestruction() {
        $this->provider->destroyVM($this->virtufile->config["vm"]["name"]);
    }

    protected function currentStateIsDestroyable() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $destroyables = $this->provider->getDestroyableStates();
        if ($this->provider->isVMInStatus($this->virtufile->config["vm"]["name"], $destroyables) == true) {
            $logging->log("This VM is in a Destroyable state...", $this->getModuleName()) ;
            return true ; }
        $logging->log("This VM is not in a Destroyable state...", $this->getModuleName()) ;
        return false ;
    }

}