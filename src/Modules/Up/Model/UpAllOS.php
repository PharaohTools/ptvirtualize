<?php

Namespace Model;

class UpAllOS extends BaseFunctionModel {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default") ;

    protected $source ;

    public function __construct($params) {
        parent::__construct($params);
        $this->initialize();
    }

    public function doUp() {
        $this->loadFiles();
        $this->findProvider("UpOther");
        $this->setLogSource();
        $o = $this->virtufile ;
        if (property_exists($o, "files")) { $this->doMultiUp() ; }
        else { $this->doSingleUp() ; }
    }

    public function setLogSource() {
        $this->loadFiles();
        $this->findProvider("UpOther");
        $this->source = (isset($this->params["pfile"]) && $this->params["pfile"] != "Virtufile" ) ? $this->params["pfile"] : "" ;
    }

    protected function doSingleUp() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if ($this->isSavedInPapyrus()) {
            if ($this->vmExistsInProvider()) {
                if ($this->vmIsRunning()) {
                    $logging->log("This VM is already up and running.", $this->source);
                    return; }
                $logging->log("Virtualize will start and optionally modify and provision your existing VM.", $this->source);
                $this->runHook("up", "pre") ;
                $this->modifyVm(true);
                $this->startVm();
                $this->provisionVm(true);
                $this->runHook("up", "post") ;
                $this->postUpMessage();
                return ;}
            $logging->log("This VM has been deleted outside of Virtualize. Re-creating from scratch.", $this->source);
            $this->deleteFromPapyrus();
            $this->completeBuildUp();
            return ; }
        $logging->log("This VM does not exist in your Papyrus file. Creating from scratch.", $this->source);
        $this->completeBuildUp();
    }

    protected function doMultiUp() {
        $loggingFactory = new \Model\Logging() ;
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Using multiple Virtufile setup", $this->source);
        // Please enter the following...
        // All boxes ?
        if (!isset($this->params["up-all"])) {
            $question = "Bring all Boxes up?" ;
            $this->params["up-all"] = self::askYesOrNo($question) ; }
        // Which boxes? (if yes above ignore this)
        if ($this->params["up-all"] == false && !isset($this->params["pfiles"])) {
            $question = "Which Virtufile/s should I execute (Comma-Separated)?" ;
            $this->params["pfiles"] = self::askForInput($question) ; }
        if ($this->params["up-all"] == true) { $pfilesToExecute = $this->virtufile->files ; }
        else { $pfilesToExecute = $this->params["pfiles"] ; }
        $ptconfigureCommand = "ptconfigure parallax cli --yes --guess " ;
        for ($i = 0; $i < count($pfilesToExecute); $i++) {
            $cnum = $i + 1 ;
            $ptconfigureCommand .= "--command-{$cnum}=\"ptvirtualize up now --yes --guess --pfile={$this->virtufile->files[$i]} \" " ; }
        echo $ptconfigureCommand."\n" ;
        self::executeAndOutput($ptconfigureCommand) ;
    }

    public function doReload() {
        $this->loadFiles();
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Halting Machine...", $this->source);
        $haltFactory = new \Model\Halt() ;
        $halt = $haltFactory->getModel($this->params) ;
        $halt->haltNow();
        $logging->log("Bringing Machine up with Modifications and Provisioning...", $this->source);
        $this->doUp();
    }

    protected function postUpMessage() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("{$this->virtufile->config["vm"]["post_up_message"]}", $this->source);
    }

    protected function completeBuildUp() {
        $this->runHook("up", "pre") ;
        $this->importBaseBox();
        $this->modifyVm();
        $this->startVm();
        $this->provisionVm();
        $this->runHook("up", "post") ;
        $this->postUpMessage();
    }

    protected function isSavedInPapyrus() {
        if ( count($this->papyrus)>1 ) { return true ; }
        return false ;
    }

    protected function vmExistsInProvider() {
        return $this->provider->vmExists($this->virtufile->config["vm"]["name"]);
    }

    protected function vmIsRunning() {
        return $this->provider->vmIsRunning($this->virtufile->config["vm"]["name"]);
    }

    protected function importBaseBox() {
        $upFactory = new \Model\Up();
        $importBox = $upFactory->getModel($this->params, "ImportBaseBox") ;
        $importBox->papyrus = $this->papyrus ;
        $importBox->virtufile = $this->virtufile ;
        $importBox->performImport() ;
    }

    protected function modifyVm($onlyIfRequestedByParam = false) {
        if ($onlyIfRequestedByParam == true) {
            if (!isset($this->params["modify"]) || (isset($this->params["modify"]) && $this->params["modify"] != true) ) {
                $loggingFactory = new \Model\Logging();
                $logging = $loggingFactory->getModel($this->params) ;
                $logging->log("Not modifying as modify parameter not set", $this->source);
                return ; } }
        $upFactory = new \Model\Up();
        $modifyVM = $upFactory->getModel($this->params, "ModifyVM") ;
        $modifyVM->papyrus = $this->papyrus ;
        $modifyVM->virtufile = $this->virtufile ;
        $modifyVM->performModifications() ;
    }

    protected function startVm() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (isset($this->virtufile->config["vm"]["gui_mode"])) {
            $logging->log("Using {$this->virtufile->config["vm"]["gui_mode"]} GUI mode specified in Virtufile", $this->source);
            $guiMode = $this->virtufile->config["vm"]["gui_mode"] ; }
        else {
            if (isset($this->params["guess"])) {
                $logging->log("No GUI mode explicitly set, Guess parameter set, defaulting to headless GUI mode...", $this->source);
                $guiMode = "headless" ; }
            else {
                $logging->log("No GUI mode or Guess parameter set, defaulting to headless GUI mode...", $this->source);
                $guiMode = "headless" ; } }
        $command = VBOXMGCOMM." startvm {$this->virtufile->config["vm"]["name"]} --type $guiMode" ;
        $this->executeAndOutput($command);
        return true ;
    }

    protected function provisionVm($onlyIfRequestedByParam = false) {
        if ($onlyIfRequestedByParam == true) {
            if (!isset($this->params["provision"]) || (isset($this->params["provision"]) && $this->params["provision"] != true) ) {
                $loggingFactory = new \Model\Logging();
                $logging = $loggingFactory->getModel($this->params) ;
                $logging->log("Not provisioning as provision parameter not set", $this->source);
                return ; } }
        $provisionFactory = new \Model\Provision();
        $provision = $provisionFactory->getModel($this->params) ;
        $provision->provisionNow();
    }

    protected function deleteFromPapyrus() {
        \Model\AppConfig::deleteProjectVariable($this->virtufile->config["vm"]["name"], null, null, true) ;
    }

    protected function runHook($hook, $type) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (isset($this->params["ignore-hooks"]) ) {
            $loggingFactory = new \Model\Logging();
            $logging = $loggingFactory->getModel($this->params) ;
            $logging->log("Not provisioning $hook $type hooks as ignore hooks parameter is set", $this->source);
            return ; }
        $logging->log("Provisioning $hook $type hooks", $this->source);
        $provisionFactory = new \Model\Provision();
        $provision = $provisionFactory->getModel($this->params) ;
        $provision->provisionHook($hook, $type);
    }

}