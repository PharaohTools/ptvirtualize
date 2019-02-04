<?php

Namespace Model;

class StatusLinuxMac extends BaseFunctionModel {

    // Compatibility
    public $os = array("Linux", 'Darwin') ;
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

    public function listVms() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
//        $res = $this->loadFiles();
//        if ($res === false) {
//            $logging->log("Status module was unable to load a Virtufile", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
//            return false ; }
//        $this->findProvider("BoxStatus");
//        $logging->log("Looking for Virtufiles...", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
        $timefile = '/tmp/vf'.time() ;
        //
        $home = $_SERVER['HOME'] ;
        $default_directories = array($home, '/opt/') ;
        $vms = array() ;

        foreach ($default_directories as $default_directory) {

            $comm = 'find '.$default_directory.' -name Virtufile 2>&1 > '.$timefile ;
            ob_start() ;
            $res = self::executeAndGetReturnCode($comm) ;
            $empty = ob_get_clean();

            if ($res === false) {
                return false ;}

            $raw = file_get_contents($timefile) ;
            $lines = explode("\n", $raw) ;
            foreach ($lines as $line) {
                if (strpos($line, 'find: ') === 0) {
                    // nt a vm
                } else {
                    $vms[] = $line ;
                }
            }
        }
        return $vms;
    }

}