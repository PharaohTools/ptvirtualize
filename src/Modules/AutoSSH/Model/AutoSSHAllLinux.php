<?php

Namespace Model;

class AutoSSHAllLinux extends BaseLinuxApp {

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

    public function autoSSHCli() {
        $this->loadFiles();
        // try the connection
        $thisPort = (isset($this->papyrus["port"])) ? : 22 ;
        $sshWorks = $this->waitForSsh($this->papyrus["target"], $thisPort);

        if ($sshWorks == true) {
            $sshParams = $this->params ;
            // try papyrus first. if box specified in phlagrantfile exists there, try its connection details.
            $srv = array(
                "user" => $this->papyrus["username"] ,
                "password" => $this->papyrus["password"] ,
                "target" => $this->papyrus["target"] );;
            $sshParams["yes"] = true ;
            $sshParams["guess"] = true ;
            $sshParams["servers"] = serialize(array($srv)) ;
            if (isset($this->papyrus["port"])) {
                $srv["port"] =
                    (isset($this->papyrus["port"]))
                    ? $this->papyrus["port"] : 22; }
            if (isset($this->papyrus["timeout"])) {
                $srv["timeout"] = $this->papyrus["timeout"] ; }

            $sshFactory = new \Model\Invoke();
            $ssh = $sshFactory->getModel($sshParams) ;
            $ssh->performInvokeSSHShell() ;
            return true ;
        }

        // if it doesn't work, try phlagrantfile connection details
        // try the connection
        $thisPort = (isset($this->phlagrantfile->config["ssh"]["port"])) ? : 22 ;
        $sshWorks = $this->waitForSsh($this->phlagrantfile->config["ssh"]["target"], $thisPort);

        if ($sshWorks == true) {
            $sshParams = $this->params ;
            $srv = array(
                "user" => $this->phlagrantfile->config["ssh"]["username"] ,
                "password" => $this->phlagrantfile->config["ssh"]["password"] ,
                "target" => $this->phlagrantfile->config["ssh"]["target"] );
            if (isset($this->phlagrantfile->config["ssh"]["port"])) {
                $sshParams["port"] = $this->phlagrantfile->config["ssh"]["port"] ; }
            if (isset($this->phlagrantfile->config["ssh"]["timeout"])) {
                $sshParams["timeout"] = $this->phlagrantfile->config["ssh"]["timeout"] ; }
            $sshParams["yes"] = true ;
            $sshParams["guess"] = true ;
            $sshParams["servers"] = serialize(array($srv)) ;
            if (isset($this->phlagrantfile->config["ssh"]["port"])) {
                $sshParams["port"] = $this->phlagrantfile->config["ssh"]["port"] ; }
            if (isset($this->phlagrantfile->config["ssh"]["timeout"])) {
                $sshParams["timeout"] = $this->phlagrantfile->config["ssh"]["timeout"] ; }
            $sshFactory = new \Model\Invoke();
            $ssh = $sshFactory->getModel($sshParams) ;
            $ssh->performInvokeSSHShell() ;
            return true ;
        }

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

    // @todo provisioners should have their own modules, and the pharoahtools code should go there
    protected function waitForSsh($ip, $thisPort=22) {
        $t = 0;
        $totalTime = (isset($this->phlagrantfile->config["vm"]["ssh_find_timeout"]))
            ? $this->phlagrantfile->config["vm"]["ssh_find_timeout"] : 300 ;
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $logging->log("Waiting for ssh...") ;
        while ($t < $totalTime) {
            $command = "cleopatra port is-responding --ip=$ip --port-number=$thisPort" ;
            $vmInfo = self::executeAndLoad($command) ;
            if (strpos($vmInfo, "Port: Success") != false) {
                $logging->log("IP $ip and Port $thisPort are responding, we'll use those...") ;
                return true; }
            echo "." ;
            $t = $t+1;
            sleep(1) ; }
        return null ;
    }

}