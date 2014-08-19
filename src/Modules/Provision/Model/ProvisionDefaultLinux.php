<?php

Namespace Model ;

class ProvisionDefaultLinux extends Base {

    public $phlagrantfile;
    public $papyrus ;
    protected $provisionModel ;

    // @todo this should support other provisioners than pharoah, provide some override here to allow
    // @todo chef solo, puppet agent, salt or ansible to get invoked
    public function provision() {
        $provisionOuts = array() ;
        $pharoahSpellings = array("Pharaoh", "pharaoh", "PharaohTools", "pharaohTools", "Pharoah", "pharoah", "PharoahTools", "pharoahTools") ;
        //var_dump("pr:", $this->phlagrantfile->config["vm"]["provision"]) ;
        foreach ($this->phlagrantfile->config["vm"]["provision"] as $provisionerSettings) {
            //var_dump($provisionerSettings["tool"], $provisionerSettings["target"], "isin: ", in_array($provisionerSettings["provisioner"], $pharoahSpellings)) ;
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
