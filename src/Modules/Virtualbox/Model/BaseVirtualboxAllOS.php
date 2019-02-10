<?php

Namespace Model;

class BaseVirtualboxAllOS extends BaseFunctionModel {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Base") ;

    public function isAvailable() {
        $command = VBOXMGCOMM ;
        $rc = $this->executeAndGetReturnCode($command, false, null, true);
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if ($rc['rc'] == 0) {
//            $logging->log("Virtualbox is available on this system", $this->getModuleName()) ;
            return true ; }
        else {
            $logging->log("Virtualbox is not available on this system", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
            return false ; }
    }

    public function isVMInStatus($vm, $statusRequested) {
        $command = VBOXMGCOMM." showvminfo \"{$vm}\" " ;
        $out = $this->executeAndLoad($command);
        $outLines = explode("\n", $out);
        $outStr = "" ;
        foreach ($outLines as $outLine) {
            if (strpos($outLine, "State:") !== false) {
                $outStr .= $outLine."\n" ;
                break; } }
        if (!is_array($statusRequested)) {$statusRequested = array($statusRequested);}
        foreach ($statusRequested as $stat) {
            if (strpos($outStr, strtolower($stat))==true) {
                return true ; } }
        return false ;
    }

}