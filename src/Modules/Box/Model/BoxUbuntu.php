<?php

Namespace Model;

class BoxUbuntu extends BaseLinuxApp {

    // Compatibility
    public $os = array("Linux") ;
    public $linuxType = array("Debian") ;
    public $distros = array("Ubuntu") ;
    public $versions = array("11.04", "11.10", "12.04", "12.10", "13.04") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("BoxAdd") ;
    protected $actionsToMethods ;

    public function __construct($params) {
        parent::__construct($params);
        $this->actionsToMethods = $this->setActionsToMethods() ;
        $this->autopilotDefiner = "Box" ;
        $this->installCommands = array("apt-get install -y ufw") ;
        $this->uninstallCommands = array("apt-get remove -y ufw") ;
        $this->programDataFolder = "" ;
        $this->programNameMachine = "box" ; // command and app dir name
        $this->programNameFriendly = "!Box!!" ; // 12 chars
        $this->programNameInstaller = "Box" ;
        $this->initialize();
    }

    protected function setActionsToMethods() {
        return array(
            "add" => "performBoxAdd"
        ) ;
    }

    protected function performBoxAdd() {
        // box add
        // get the .pbox file (if remote)
        // get save location
        // copy it there
        // untar the single metadata.json file out of it
        // check the provider
        // load the provider and invoke the add box method there
        // - vbix module
        //  - untar it there
        //  - import it
        return $this->setDefault();
    }

    public function setBoxRule() {
        if (isset($this->params["box-rule"])) {
            $boxRule = $this->params["box-rule"]; }
        else {
            $boxRule = self::askForInput("Enter Box Rule:", true); }
        $this->boxRule = $boxRule ;
    }

    public function setDefaultPolicyParam() {
        $opts =  array("allow", "deny", "reject") ;
        if (isset($this->params["policy"]) && in_array($this->params["policy"], $opts)) {
            $loggingFactory = new \Model\Logging();
            $logging = $loggingFactory->getModel($this->params);
            $logging->log("Policy param for set default must be allow, deny or reject") ;
            $defaultPolicy = $this->params["policy"]; }
        else {
            $defaultPolicy = self::askForArrayOption("Enter Policy:", $opts, true); }
        $this->defaultPolicy = $defaultPolicy ;
    }

    public function enable() {
        $out = $this->executeAndOutput("sudo ufw --force enable");
        if (strpos($out, "enabled") == false ) {
            $loggingFactory = new \Model\Logging();
            $logging = $loggingFactory->getModel($this->params);
            $logging->log("Box Enable command did not execute correctly") ;
            return false ; }
        return true ;
    }

}