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
        $logging->log("Status module was unable to load a Virtufile", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
        $comm = 'cd/ && dir /s/b Virtufile ' ;
        $res = self::executeAndLoad($comm) ;
        if ($res === '') { return false ; }
        $raw = $res ;
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