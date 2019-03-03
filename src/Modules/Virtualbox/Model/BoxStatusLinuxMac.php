<?php

Namespace Model;

class BoxStatus extends BaseVirtualboxAllOS {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("BoxStatus") ;

    public function statusShow($name) {
        $command = VBOXMGCOMM." showvminfo \"{$name}\"  " ;
        $out = $this->executeAndLoad($command);
        $outLines = explode("\n", $out);
        foreach ($outLines as $outLine) {
            if (strpos($outLine, "State:") !== false) {
                return $outLine."\n" ; } }
        return null ;
    }

    public function statusFull($name) {
        $command = VBOXMGCOMM." showvminfo \"{$name}\" 2>/dev/null" ;
        $status = $this->executeAndLoad($command) ;
        return $status ;
    }

    public function statusData($name) {
        $command = VBOXMGCOMM." showvminfo \"{$name}\" --machinereadable 2>/dev/null" ;
        $status_string = $this->executeAndLoad($command) ;
        $lines = explode("\n", $status_string) ;
        $status = [] ;
        foreach ($lines as $line) {
            $pair = explode("=", $line) ;
            if (isset($pair[0]) && isset($pair[1])) {
                $status[$pair[0]] = $pair[1] ;
            }
        }
        return $status ;
    }

    public function getRunStateFromData($result_object) {
        ob_start() ;
        var_dump($result_object) ;
        $out = ob_get_clean() ;
        file_put_contents('/tmp/thisout', $out) ;
        if ($result_object['vm_status_output'] === []) {
            return false ;
        }
        if ($result_object['vm_status_output']['VMState'] === '"running"') {
            return true ;
        }
        return false ;
    }

    public function existsInProvider($result_object) {
        ob_start() ;
        var_dump($result_object) ;
        $out = ob_get_clean() ;
        file_put_contents('/tmp/thisout', $out) ;
        if (isset($result_object['vm_status_output']) &&
            is_array($result_object['vm_status_output']) &&
            count($result_object['vm_status_output'])>0) {
            return true ;
        }
        return false ;
    }

}