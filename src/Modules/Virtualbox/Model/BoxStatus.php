<?php

Namespace Model;

class BoxStatus extends BaseFunctionModel {

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
        $command = VBOXMGCOMM." showvminfo \"{$name}\" " ;
        $status = $this->executeAndLoad($command) ;
        return $status ;
    }

}