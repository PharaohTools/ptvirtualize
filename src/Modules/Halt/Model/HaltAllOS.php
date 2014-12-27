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
        $logging->log("Checking current state...") ;
        if ($this->currentStateIsHaltable() == false) { return ; }
        $logging->log("Attempting soft power off by button...") ;
        $logging->log("Waiting at least {$this->phlagrantfile->config["vm"]["graceful_halt_timeout"]} seconds for machine to power off...") ;
        $this->provider->haltSoft($this->phlagrantfile->config["vm"]["name"]);
        if ($this->waitForStatus("powered off", $this->phlagrantfile->config["vm"]["graceful_halt_timeout"], "3")==true) {
            $logging->log("Successful soft power off by button...") ;
            return true ; }
        else {
            $logging->log("Failed soft power off by button, attempting SSH shutdown.") ;

            $sshParams = $this->params ;

            $srv = array(
                "user" => $this->papyrus["username"] ,
                "password" => $this->papyrus["password"] ,
                "target" => $this->papyrus["target"] );
            $sshParams["yes"] = true ;
            $sshParams["guess"] = true ;
            $sshParams["servers"] = serialize(array($srv)) ;
            $sshParams["ssh-data"] = "echo {$this->phlagrantfile->config["ssh"]["password"]} | sudo -S shutdown now\n";

            if (isset($this->phlagrantfile->config["ssh"]["port"])) {
                $sshParams["port"] = $this->phlagrantfile->config["ssh"]["port"] ; }
            if (isset($this->phlagrantfile->config["ssh"]["timeout"])) {
                $sshParams["timeout"] = $this->phlagrantfile->config["ssh"]["timeout"] ; }
            $sshFactory = new \Model\Invoke();
            $ssh = $sshFactory->getModel($sshParams) ;
            $ssh->performInvokeSSHData() ;

            $logging->log("Attempting shutdown by SSH...") ;
            $logging->log("Waiting at least {$this->phlagrantfile->config["vm"]["ssh_halt_timeout"]} seconds for machine to power off...") ;

            if ($this->waitForStatus("powered off", $this->phlagrantfile->config["vm"]["ssh_halt_timeout"], "3")==true) {
                $logging->log("Successful power off SSH Shutdown...") ;
                return true ; } }
        if (isset($this->params["fail-hard"])) {
            $lmsg = "Attempts to Halt this box by both Soft Power off and SSH Shutdown have failed. You have used the " .
                "--fail-hard flag to do hard power off now." ;
            $logging->log($lmsg) ;
            $this->provider->haltHard($this->phlagrantfile->config["vm"]["name"]);
            return true ; }
        $lmsg = "Attempts to Halt this box by both Soft Power off and SSH Shutdown have failed. You may need to use ".
            "phlagrant halt hard. You can also use the parameter --fail-hard to do this automatically." ;
        $logging->log($lmsg) ;
        return false ;

    }

    public function haltPause() {
        $this->loadFiles();
        $this->findProvider("BoxHalt");
        $this->provider->haltPause($this->phlagrantfile->config["vm"]["name"]);
    }

    public function haltHard() {
        $this->loadFiles();
        $this->findProvider("BoxHalt");
        $this->provider->haltHard($this->phlagrantfile->config["vm"]["name"]);
    }

    protected function currentStateIsHaltable() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $haltables = $this->provider->getHaltableStates();
        if ($this->provider->isVMInStatus($this->phlagrantfile->config["vm"]["name"], $haltables) == true) {
            $logging->log("This VM is in a Haltable state...") ;
            return true ; }
        $logging->log("This VM is not in a Haltable state...") ;
        return false ;
    }

    # @todo in_array or something to check a sane status was requested
    protected function waitForStatus($statusRequested, $total_time, $interval) {
        for ($i=0; $i<$total_time; $i=$i+$interval) {
            if($this->provider->isVMInStatus($this->phlagrantfile->config["vm"]["name"], $statusRequested)) {
                return true ; }
            echo "." ;
            sleep($interval); }
        echo "\n" ;
        return false ;
    }

}
