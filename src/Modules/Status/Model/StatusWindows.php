<?php

Namespace Model;

class StatusWindows extends StatusLinuxMac {

    // Compatibility
    public $os = array("Windows", 'WINNT') ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default") ;

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
        $comm = 'find / -name Virtufile 2>&1 > '.$timefile ;
        $res = self::executeAndGetReturnCode($comm) ;

        if ($res === false) {
            return false ;}

        $raw = file_get_contents($timefile) ;
        $lines = explode("\n", $raw) ;
        $vms = array() ;
        foreach ($lines as $line) {
            if (strpos($line, 'find: ') === 0) {
                // nt a vm
            } else {
                $vms[] = $line ;
            }
        }
        return $vms;
    }

}