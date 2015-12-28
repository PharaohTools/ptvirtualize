<?php

Namespace Model;

class ResumeAllOS extends BaseFunctionModel {

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
        $logging->log("Checking current state...", $this->getModuleName()) ;
        if ($this->currentStateIsResumable() == false) { return false ; }
        $logging->log("Attempting Resume...", $this->getModuleName()) ;
        $this->runHook("resume", "pre") ;
        $res = $this->provider->resume($this->virtufile->config["vm"]["name"]);
        $this->runHook("resume", "post") ;
        return $res ;
    }

    protected function currentStateIsResumable() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $resumables = $this->provider->getResumableStates();
        if ($this->provider->isVMInStatus($this->virtufile->config["vm"]["name"], $resumables) == true) {
            $logging->log("This VM is in a resumable state...", $this->getModuleName()) ;
            return true ; }
        $logging->log("This VM is not in a resumable state...", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
        return false ;
    }

}