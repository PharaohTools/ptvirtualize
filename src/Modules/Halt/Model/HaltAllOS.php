<?php

Namespace Model;

class HaltAllOS extends BaseFunctionModel {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default") ;

    public function __construct($params) {
        parent::__construct($params);
        $this->initialize();
    }

    public function haltNow() {
        $this->loadFiles();
        $this->findProvider("BoxHalt");
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $logging->log("Checking current state...", $this->getModuleName()) ;
        if ($this->currentStateIsHaltable() == false) { return false ; }
        $this->runHook("halt", "pre") ;
        $logging->log("Attempting soft power off by button...", $this->getModuleName()) ;
        $logging->log("Waiting at least {$this->virtufile->config["vm"]["graceful_halt_timeout"]} seconds for machine to power off...", $this->getModuleName()) ;
        $this->provider->haltSoft($this->virtufile->config["vm"]["name"]);
        if ($this->waitForStatus("powered off", $this->virtufile->config["vm"]["graceful_halt_timeout"], "3")==true) {
            $logging->log("Successful soft power off by button...") ;
            return true ; }
        else {
            $logging->log("Failed soft power off by button, attempting SSH shutdown.", $this->getModuleName()) ;

            $sshParams = $this->params ;

            $srv = array(
                "user" => $this->virtufile->config["ssh"]["user"] ,
                "password" => $this->virtufile->config["ssh"]["password"] ,
                "target" => $this->virtufile->config["ssh"]["target"]  ,
                "port" => $this->virtufile->config["ssh"]["port"]  ,
                "driver" => $this->virtufile->config["ssh"]["driver"] );
            $sshParams["yes"] = true ;
            $sshParams["guess"] = true ;
            $sshParams["servers"] = serialize(array($srv)) ;
            $sshParams["ssh-data"] = "echo {$this->virtufile->config["ssh"]["password"]} | sudo -S shutdown now\n";

            if (isset($this->virtufile->config["ssh"]["port"])) {
                $sshParams["port"] = $this->virtufile->config["ssh"]["port"] ; }
            if (isset($this->virtufile->config["ssh"]["timeout"])) {
                $sshParams["timeout"] = $this->virtufile->config["ssh"]["timeout"] ; }
            $sshFactory = new \Model\Invoke();
            $ssh = $sshFactory->getModel($sshParams) ;
            $ssh->performInvokeSSHData() ;

            $logging->log("Attempting shutdown by SSH...", $this->getModuleName()) ;
            $logging->log("Waiting at least {$this->virtufile->config["vm"]["ssh_halt_timeout"]} seconds for machine to power off...", $this->getModuleName()) ;

            if ($this->waitForStatus("powered off", $this->virtufile->config["vm"]["ssh_halt_timeout"], "3")==true) {
                $logging->log("Successful power off SSH Shutdown...", $this->getModuleName()) ;
                $this->runHook("halt", "post") ;
                return true ; } }
        if (isset($this->params["fail-hard"])) {
            $lmsg = "Attempts to Halt this box by both Soft Power off and SSH Shutdown have failed. You have used the " .
                "--fail-hard flag to do hard power off now." ;
            $logging->log($lmsg, $this->getModuleName()) ;
            $this->provider->haltHard($this->virtufile->config["vm"]["name"]);
            $this->runHook("halt", "post") ;
            return true ; }
        $lmsg = "Attempts to Halt this box by both Soft Power off and SSH Shutdown have failed. You may need to use ".
            "ptvirtualize halt hard. You can also use the parameter --fail-hard to do this automatically." ;
        $logging->log($lmsg, $this->getModuleName()) ;
        $this->runHook("halt", "post") ;
        return false ;

    }

    public function haltPause() {
        $this->loadFiles();
        $this->findProvider("BoxHalt");
        $this->provider->haltPause($this->virtufile->config["vm"]["name"]);
    }

    public function haltHard() {
        $this->loadFiles();
        $this->findProvider("BoxHalt");
        $this->provider->haltHard($this->virtufile->config["vm"]["name"]);
    }

    protected function currentStateIsHaltable() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $haltables = $this->provider->getHaltableStates();
        if ($this->provider->isVMInStatus($this->virtufile->config["vm"]["name"], $haltables) == true) {
            $logging->log("This VM is in a Haltable state...", $this->getModuleName()) ;
            return true ; }
        $logging->log("This VM is not in a Haltable state...", $this->getModuleName()) ;
        return false ;
    }

    # @todo in_array or something to check a sane status was requested
    protected function waitForStatus($statusRequested, $total_time, $interval) {
        for ($i=0; $i<$total_time; $i=$i+$interval) {
            if($this->provider->isVMInStatus($this->virtufile->config["vm"]["name"], $statusRequested)) {
                return true ; }
            echo "." ;
            sleep($interval); }
        echo "\n" ;
        return false ;
    }

}
