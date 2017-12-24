<?php

Namespace Model;

class StatusAllOS extends BaseFunctionModel {

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

    public function statusShow() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $res = $this->loadFiles();
        if ($res === false) {
            $logging->log("Status module was unable to load a Virtufile", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
            return false ; }
        $this->findProvider("BoxStatus");
        return $this->provider->statusShow($this->virtufile->config["vm"]["name"]);
    }

    public function statusFull() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $res = $this->loadFiles();
        if ($res === false) {
            $logging->log("Status module was unable to load a Virtufile", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
            return false ; }
        $this->findProvider("BoxStatus");
        return $this->provider->statusFull($this->virtufile->config["vm"]["name"]);
    }

}