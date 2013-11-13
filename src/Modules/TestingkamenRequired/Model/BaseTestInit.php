<?php

Namespace Model;

class BaseTestInit extends Base {

    protected $initCommands;
    protected $programExecutorCommand;

    public function __construct($params) {
        parent::__construct($params);
        $this->populateCompletion();
    }

    public function initialize() {
        $this->populateTitle();
    }

    public function askInit() {
        return $this->askWhetherToInit();
    }

    public function askWhetherToInit() {
        return $this->performInit();
    }

    public function runAutoPilotInit($autoPilot) {
        return $this->runAutoPilotAnyInit($autoPilot);
    }

    private function performInit() {
        $doInstall = (isset($this->params["yes"]) && $this->params["yes"]==true) ?
            true : $this->askWhetherToInitToScreen();
        if (!$doInstall) { return false; }
        return $this->init();
    }

    public function runAutoPilotAnyInit($autoPilot){
        $this->setAutoPilotVariables($autoPilot);
        $this->init($autoPilot);
        return true;
    }

    public function init($autoPilot = null) {
        $this->showTitle();
        $this->executePreInstallFunctions($autoPilot);
        $this->doInitCommand();
        $this->executePostInstallFunctions($autoPilot);
        $this->extraCommands();
        $this->showCompletion();
        return true;
    }

    private function showTitle() {
        print $this->titleData ;
    }

    private function showCompletion() {
        print $this->completionData ;
    }

    private function askWhetherToInitToScreen(){
        $question = "Run ".$this->programNameFriendly."?";
        return self::askYesOrNo($question);
    }

    private function doInitCommand(){
        self::swapCommandArrayPlaceHolders($this->initCommands);
        self::executeAsShell($this->initCommands);
    }

}