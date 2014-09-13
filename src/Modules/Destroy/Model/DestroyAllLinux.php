<?php

Namespace Model;

class DestroyAllLinux extends BaseLinuxApp {

    // Compatibility
    public $os = array("Linux") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default") ;

    protected $phlagrantfile;
    protected $papyrus ;

    public function __construct($params) {
        parent::__construct($params);
        $this->initialize();
    }

    public function destroyNow() {
        $this->loadFiles();
        $this->runHook("pre") ;
        $this->removeShares();
        $command = "VBoxManage unregistervm {$this->phlagrantfile->config["vm"]["name"]} --delete" ;
        $this->executeAndOutput($command);
        $this->runHook("post") ;
        $this->deleteFromPapyrus() ;
    }

    protected function deleteFromPapyrus() {
        \Model\AppConfig::deleteProjectVariable("phlagrant-box", null, null, true) ;
    }

    protected function removeShares() {
        $upFactory = new \Model\Up();
        $modifyVM = $upFactory->getModel($this->params, "ModifyVM") ;
        $modifyVM->papyrus = $this->papyrus ;
        $modifyVM->phlagrantfile = $this->phlagrantfile ;
        $modifyVM->removeShares() ;
    }

    protected function runHook($type) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (isset($this->params["ignore-hooks"]) ) {
            $loggingFactory = new \Model\Logging();
            $logging = $loggingFactory->getModel($this->params) ;
            $logging->log("Not provisioning destroy hooks as ignore hooks parameter is set");
            return ; }
        $ut = ucfirst($type) ;
        $logging->log("Provisioning $ut Destroy Hooks");
        $provisionFactory = new \Model\Provision();
        $provision = $provisionFactory->getModel($this->params) ;
        $provision->provisionHook("destroy", $type);
    }

    protected function loadFiles() {
        $this->phlagrantfile = $this->loadPhlagrantFile();
        $this->papyrus = $this->loadPapyrusLocal();
    }

    protected function loadPhlagrantFile() {
        $prFactory = new \Model\PhlagrantRequired();
        $phlagrantFileLoader = $prFactory->getModel($this->params, "PhlagrantFileLoader") ;
        return $phlagrantFileLoader->load() ;
    }

    protected function loadPapyrusLocal() {
        $prFactory = new \Model\PhlagrantRequired();
        $papyrusLocalLoader = $prFactory->getModel($this->params, "PapyrusLocalLoader") ;
        return $papyrusLocalLoader->load() ;
    }

}