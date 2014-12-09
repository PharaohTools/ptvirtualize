<?php

Namespace Model;

class HaltAllOS extends BaseLinuxApp {

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

    public function haltNow() {
        $this->loadFiles();
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        if ($this->currentStateIsHaltable() == false) { return ; }
        $logging->log("Checking current state...") ;
        $logging->log("Attempting soft power off by button...") ;
        $logging->log("Waiting at least {$this->phlagrantfile->config["vm"]["graceful_halt_timeout"]} seconds for machine to power off...") ;
        $command = VBOXMGCOMM." controlvm {$this->phlagrantfile->config["vm"]["name"]} acpipowerbutton" ;
        $this->executeAndOutput($command);
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
            $command = VBOXMGCOMM." controlvm {$this->phlagrantfile->config["vm"]["name"]} poweroff" ;
            $this->executeAndOutput($command);
            return true ; }
        $lmsg = "Attempts to Halt this box by both Soft Power off and SSH Shutdown have failed. You may need to use ".
            "phlagrant halt hard. You can also use the parameter --fail-hard to do this automatically." ;
        $logging->log($lmsg) ;
        return false ;

    }

    public function haltPause() {
        $this->loadFiles();
        $command = VBOXMGCOMM." controlvm {$this->phlagrantfile->config["vm"]["name"]} pause" ;
        $this->executeAndOutput($command);
    }

    public function haltHard() {
        $this->loadFiles();
        $command = VBOXMGCOMM." controlvm {$this->phlagrantfile->config["vm"]["name"]} poweroff" ;
        $this->executeAndOutput($command);
    }

    protected function currentStateIsHaltable() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $status = $this->isVMInStatus("running") ;
        if ($status == true) {
            $logging->log("This VM is in a Haltable state...") ;
            return true ; }
        $logging->log("This VM is not in a Haltable state...") ;
        return false ;
    }

    # @todo in_array or something to check a sane status was requested
    protected function waitForStatus($statusRequested, $total_time, $interval) {
        for ($i=0; $i<$total_time; $i=$i+$interval) {
            if($this->isVMInStatus($statusRequested)) {
                return true ; }
            echo "." ;
            sleep($interval); }
        echo "\n" ;
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
