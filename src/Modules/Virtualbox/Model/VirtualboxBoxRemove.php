<?php

Namespace Model;

class VirtualboxBoxRemove extends BaseVirtualboxAllOS {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("BoxRemove") ;

    public function removeBox($target, $name) {
        // add the box here
        // remove the directory for the box
        $this->removeBoxDirectory($target, $name) ;
        $this->completion() ;
    }

    protected function askForBoxRemoveExecute() {
        if (isset($this->params["yes"]) && $this->params["yes"]==true) { return true ; }
        $question = 'Remove Virtualbox Server Boxes?';
        return self::askYesOrNo($question);
    }

    // @todo this wont work on windows
    protected function removeBoxDirectory($target, $name) {
        $boxdir = $target . $name ;
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $command = "whoami" ;
        $whoami = self::executeAndLoad($command);
        $whoami = str_replace("\n", "", $whoami);
        $whoami = str_replace("\r", "", $whoami);
        if (file_exists($boxdir)) {
            $logging->log("Files exist at $boxdir. Removing.", $this->getModuleName());
            $command = "rm -rf $boxdir" ;
            self::executeAndOutput($command); }
        else {
            $logging->log("No files exist at $boxdir. Nothing to do, exiting...", $this->getModuleName());
            return ; }
        if (!file_exists($target)) {
            $logging->log("$boxdir removed.", $this->getModuleName()); }
        else {
            $logging->log("An error occured removing $boxdir. Retrying as superuser", $this->getModuleName());
            $command = "sudo rm -rf $boxdir" ;
            self::executeAndOutput($command);
            if (!file_exists($boxdir)) {
                $logging->log("$boxdir removed.", $this->getModuleName()); }
            else {
                $logging->log("Errors occured when removing $boxdir as both $whoami and superuser", $this->getModuleName()); } }
    }

    protected function completion() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Completed Removing Box...", $this->getModuleName());
    }

}