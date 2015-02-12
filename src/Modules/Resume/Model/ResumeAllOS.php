<?php

Namespace Model;

class ResumeAllLinux extends BaseFunctionModel {

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

    public function resumeNow() {
        $this->loadFiles();
        $this->findProvider("BoxResume");
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $logging->log("Checking current state...") ;
        if ($this->currentStateIsResumable() == false) { return ; }
        $logging->log("Attempting Resume...") ;
        $this->provider->resume($this->virtufile->config["vm"]["name"]);
    }

    protected function currentStateIsResumable() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $resumables = $this->provider->getResumableStates();
        if ($this->provider->isVMInStatus($this->virtufile->config["vm"]["name"], $resumables) == true) {
            $logging->log("This VM is in a Resumable state...") ;
            return true ; }
        $logging->log("This VM is not in a Resumable state...") ;
        return false ;
    }

}