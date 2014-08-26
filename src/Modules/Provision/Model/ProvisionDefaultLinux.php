<?php

Namespace Model ;

class ProvisionDefaultLinux extends Base {

    public $phlagrantfile;
    public $papyrus ;
    protected $provisionModel ;

    // @todo this should support other provisioners than pharoah, provide some override here to allow
    // @todo chef solo, puppet agent, salt or ansible to get invoked
    public function provision($hook = "") {
        $provisionOuts = array() ;
        $pharoahSpellings = array("Pharaoh", "pharaoh", "PharaohTools", "pharaohTools", "Pharoah", "pharoah", "PharoahTools", "pharoahTools") ;
        if ($hook != "") {$hook = "_$hook" ; }
        foreach ($this->phlagrantfile->config["vm"]["provision$hook"] as $provisionerSettings) {
            if (in_array($provisionerSettings["provisioner"], array("shell", "bash", "Shell", "Bash"))) {
                $provisionObjectFactory = new \Model\Shell() ;
                $provisionObject = $provisionObjectFactory->getModel($this->params, "Provision");
                $provisionObject->phlagrantfile = $this->phlagrantfile;
                $provisionObject->papyrus = $this->papyrus;
                $provisionOuts[] = $provisionObject->provision($provisionerSettings, $this) ; }
            if (in_array($provisionerSettings["provisioner"], $pharoahSpellings)) {
                $provisionObjectFactory = new \Model\PharaohTools() ;
                $provisionObject = $provisionObjectFactory->getModel($this->params, "Provision");
                $provisionObject->phlagrantfile = $this->phlagrantfile;
                $provisionObject->papyrus = $this->papyrus;
                $provisionOuts[] = $provisionObject->provision($provisionerSettings, $this) ; } }
        return $provisionOuts ;
    }

}
