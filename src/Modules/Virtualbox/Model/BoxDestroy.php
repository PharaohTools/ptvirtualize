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
            $logging->log("Files exist at $boxdir. Removing.");
            $command = "rm -rf $boxdir" ;
            self::executeAndOutput($command); }
        else {
            $logging->log("No files exist at $boxdir. Nothing to do, exiting...");
            return ; }
        if (!file_exists($target)) {
            $logging->log("$boxdir removed."); }
        else {
            $logging->log("An error occured removing $boxdir. Retrying as superuser");
            $command = "sudo rm -rf $boxdir" ;
            self::executeAndOutput($command);
            if (!file_exists($boxdir)) {
                $logging->log("$boxdir removed."); }
            else {
                $logging->log("Errors occured when removing $boxdir as both $whoami and superuser"); } }
    }

    protected function completion() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Completed Removing Box...");
    }

}