<?php

Namespace Model;

class BoxDestroy extends BaseVirtualboxAllOS {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("BoxDestroy") ;

    public function destroyVM($name) {
        $command = VBOXMGCOMM." unregistervm {$name} --delete" ;
        $this->executeAndOutput($command);
    }

    public function getDestroyableStates() {
        return array("aborted", "powered off") ;
    }

}