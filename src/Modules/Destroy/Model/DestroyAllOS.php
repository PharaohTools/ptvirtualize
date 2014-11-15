<?php

Namespace Model;

class DestroyAllOS extends BaseLinuxApp {

    // Compatibility
    public $os = array("any") ;
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
        if ($this->currentStateIsDestroyable() == false) { return ; }
        $this->runHook("pre") ;
        $this->removeShares();
        $command = VBOXMGCOMM." unregistervm {$this->phlagrantfile->config["vm"]["name"]} --delete" ;
        $this->executeAndOutput($command);
        $this->runHook("post") ;
        $this->deleteFromPapyrus() ;
    }

    protected function deleteFromPapyrus() {
        \Model\AppConfig::deleteProjectVariable($this->phlagrantfile->config["vm"]["name"], null, null, true) ;
    }

    protected function removeShares() {
        $upFactory = new \Model\Up();
        $modifyVM = $upFactory->getModel($this->params, "ModifyVM") ;
        $modifyVM->papyrus = $this->papyrus ;
        $modifyVM->phlagrantfile = $this->phlagrantfile ;
        $modifyVM->removeShares() ;
    }

    protected function currentStateIsDestroyable() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $s1 = $this->isVMInStatus("aborted") ;
        $s2 = $this->isVMInStatus("powered off") ;
        if ($s1 == true || $s2 == true) {
            $logging->log("This VM is in a Destroyable state...") ;
            return true ; }
        $logging->log("This VM is not in a Destroyable state...") ;
        return false ;
    }

    protected function isVMInStatus($statusRequested) {
        $command = VBOXMGCOMM." showvminfo \"{$this->phlagrantfile->config["vm"]["name"]}\" " ;
        $out = $this->executeAndLoad($command);
        $outLines = explode("\n", $out);
        $outStr = "" ;
        foreach ($outLines as $outLine) {
            if (strpos($outLine, "State:") !== false) {
                $outStr .= $outLine."\n" ;
                break; } }
        $isStatusRequested = strpos($outStr, strtolower($statusRequested)) ;
        return $isStatusRequested ;
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
        return $papyrusLocalLoader->load($this->phlagrantfile) ;
    }

}