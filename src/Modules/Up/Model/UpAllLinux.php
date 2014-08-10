<?php

Namespace Model;

class UpAllLinux extends BaseLinuxApp {

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

    public function doUp() {
        $this->loadFiles();
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if ($this->isSavedInPapyrus()) {
            if ($this->vmExistsInProvider()) {
                if ($this->vmIsRunning()) {
                     $logging->log("This VM is already up and running."); }
                $logging->log("Phlagrant will start and optionally provision your existing VM.");
                $this->startVm();
                $this->provisionVm(true); }
            $logging->log("This VM has been deleted outside of Phlagrant. Re-creating from scratch.");
            $this->deleteFromPapyrus();
            $this->completeBuildUp(); }
        $logging->log("This VM does not exist in your Papyrus file. Creating from scratch.");
        $this->completeBuildUp();
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

    protected function completeBuildUp() {
        $this->createVm();
        $this->importBaseBox();
        $this->modifyVm();
        $this->startVm();
        $this->provisionVm();
    }

    protected function isSavedInPapyrus() {
        if ( count($this->papyrus)>1 ) { return true ; }
        return false ;
    }

    protected function vmExistsInProvider() {
        $out = $this->executeAndLoad("vboxmanage list vms");
        if (strpos($out, $this->papyrus["name"] != false )) {
            return true ; }
        return false ;
    }

    protected function vmIsRunning() {
        $out = $this->executeAndLoad("vboxmanage list runningvms");
        if (strpos($out, $this->papyrus["name"] != false )) {
            return true ; }
        return false ;
    }

    protected function createVm() {
        $comm  = "vboxmanage createvm --name {$this->phlagrantfile->config["vm"]["name"]} " ;
        $comm .= "--ostype {$this->phlagrantfile->config["vm"]["ostype"]} --register" ;
        $this->executeAndOutput($comm);
        return true ;
    }

    protected function importBaseBox() {
    }

    protected function modifyVm() {
        $upFactory = new \Model\Up();
        $modifyVM = $upFactory->getModel($this->params, "ModifyVM") ;
        $modifyVM->papyrus = $this->papyrus ;
        $modifyVM->phlagrantfile = $this->phlagrantfile ;
        $modifyVM->performModifications() ;
    }

    protected function startVm() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (isset($this->phlagrantfile->config["vm"]["gui_mode"])) {
            $guiMode = $this->phlagrantfile->config["vm"]["gui_mode"] ; }
        else {
            if (isset($this->params["guess"])) {
                $logging->log("No GUI mode explicitly set, Guess parameter set, defaulting to headless GUI mode...");
                $guiMode = "headless" ; }
            else {
                $logging->log("No GUI mode or Guess parameter set, defaulting to headless GUI mode...");
                $guiMode = "headless" ; } }
        $command = "vboxmanage startvm {$this->phlagrantfile->config["vm"]["name"]} --type $guiMode" ;
        echo $command ;
        $this->executeAndOutput($command);
        return true ;
    }

    protected function provisionVm($onlyIfRequestedByParam = false) {
    }

    protected function deleteFromPapyrus() {
        \Model\AppConfig::deleteProjectVariable("phlagrant-box", true) ;
    }

}