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
        $this->runDestroyHooks() ;
        $command = "VBoxManage unregistervm {$this->phlagrantfile->config["vm"]["name"]} --delete" ;
        $this->executeAndOutput($command);
        $this->deleteFromPapyrus() ;
    }

    protected function deleteFromPapyrus() {
        \Model\AppConfig::deleteProjectVariable("phlagrant-box", null, null, true) ;
    }

    protected function runDestroyHooks() {
        if (isset($this->params["ignore-hooks"]) ) {
            $loggingFactory = new \Model\Logging();
            $logging = $loggingFactory->getModel($this->params) ;
            $logging->log("Not provisioning destroy hooks as ignore hooks parameter is set");
            return ; }
        $provisionFactory = new \Model\Provision();
        $provision = $provisionFactory->getModel($this->params) ;
        $provision->provisionNow("destroy");
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