<?php

Namespace Model;

class AutoSSHAllOS extends BaseLinuxApp {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default") ;

    protected $virtufile;
    protected $papyrus ;

    public function __construct($params) {
        parent::__construct($params);
        $this->initialize();
    }

    protected function findSshParams($include_sftp = false) {

        $sshParams = $this->params ;
        // try papyrus first. if box specified in virtufile exists there, try its connection details.
        $srv = array(
            "user" => $this->virtufile->config["ssh"]["user"] ,
            "password" => $this->virtufile->config["ssh"]["password"] ,
            "target" => $this->virtufile->config["ssh"]["target"]  ,
            "driver" => $this->virtufile->config["ssh"]["driver"]  );
        $sshParams["yes"] = true ;
        $sshParams["guess"] = true ;
        if (isset($this->papyrus["port"]) || isset($this->virtufile->config["ssh"]["port"])) {

            // @todo two ternarys and an if - bleurgh
            $srv["port"] = (isset($this->virtufile->config["ssh"]["port"]))
                ? $this->virtufile->config["ssh"]["port"] :
                null ;
            $srv["port"] = ($srv["port"]==null && isset($this->papyrus["port"]))
                ? $this->papyrus["port"] :
                $srv["port"] ; }
        if ($srv["port"] == null) { $srv["port"] = 22 ;}

        if (isset($this->virtufile->config["timeout"])) {
            $srv["timeout"] = $this->virtufile->config["timeout"] ; }

        $sshParams["servers"] = serialize(array($srv)) ;

        if ($include_sftp==true) {
            $sshParams["source"] = $this->getSourceFilePath() ;
            $sshParams["target"] = $this->getTargetFilePath() ; }

        return $sshParams ;

    }

    public function autoSSHCli() {
        if ($this->loadFiles() == false) { return false; }
        $sshParams = $this->findSshParams() ;
        $sshFactory = new \Model\Invoke();
        $ssh = $sshFactory->getModel($sshParams) ;
        $ssh->performInvokeSSHShell() ;
        return true ;
    }

    // @todo this needs testing
    public function autoSSHData() {
        if ($this->loadFiles() == false) { return false; }
        $sshParams = $this->findSshParams() ;
        $sshFactory = new \Model\Invoke();
        $ssh = $sshFactory->getModel($sshParams) ;
        $ssh->performInvokeSSHData() ;
        return true ;

    }

    // @todo this needs testing
    // @todo this should work like the cli one
    public function autoSSHScript() {
        if ($this->loadFiles() == false) { return false; }
        $sshParams = $this->findSshParams() ;
        $sshFactory = new \Model\Invoke();
        $ssh = $sshFactory->getModel($sshParams) ;
        $ssh->performInvokeSSHScript() ;
        return true ;
    }

    // @todo this and method below can be rolled into one
    public function autoSFTPPut() {
        if ($this->loadFiles() == false) { return false; }
        $sshFactory = new \Model\SFTP();
        $sshParams = $this->findSshParams() ;
        $ssh = $sshFactory->getModel($sshParams) ;
        $ssh->performSFTPPut() ;
        return true ;
    }

    // @todo this and method above can be rolled into one
    public function autoSFTPGet() {
        if ($this->loadFiles() == false) { return false; }
        $sshFactory = new \Model\SFTP();
        $sshParams = $this->findSshParams() ;
        $ssh = $sshFactory->getModel($sshParams) ;
        $ssh->performSFTPGet() ;
        return true ;
    }

    public function loadFiles() {
        $this->virtufile = $this->loadVirtufile();
        $this->papyrus = $this->loadPapyrusLocal($this->virtufile);
        if (in_array(false, array($this->virtufile))) {
            \Core\BootStrap::setExitCode(1);
            $loggingFactory = new \Model\Logging();
            $logging = $loggingFactory->getModel($this->params) ;
            $logging->log("Unable to load a required file", $this->getModuleName()) ;
            return false ; }
        return true ;
    }

    protected function loadVirtufile() {
        $prFactory = new \Model\PTVirtualizeRequired();
        $ptvirtualizeFileLoader = $prFactory->getModel($this->params, "VirtufileLoader") ;
        return $ptvirtualizeFileLoader->load() ;
    }

    protected function loadPapyrusLocal() {
        $prFactory = new \Model\PTVirtualizeRequired();
        $papyrusLocalLoader = $prFactory->getModel($this->params, "PapyrusLocalLoader") ;
        return $papyrusLocalLoader->load($this->virtufile) ;
    }

    // @todo provisioners should have their own modules, and the pharaohtools code should go there
    // @todo this should get port module probably do it within app
    protected function waitForSsh($ip, $thisPort=22) {
        $t = 0;
        $totalTime = (isset($this->virtufile->config["vm"]["ssh_find_timeout"]))
            ? $this->virtufile->config["vm"]["ssh_find_timeout"] : 300 ;
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $logging->log("Waiting for ssh...", $this->getModuleName()) ;
        while ($t < $totalTime) {
            $command = PTCCOMM." port is-responding --ip=$ip --port-number=$thisPort" ;
            $vmInfo = self::executeAndLoad($command) ;
            if (strpos($vmInfo, "Port: Success") != false) {
                $logging->log("IP $ip and Port $thisPort are responding, we'll use those...", $this->getModuleName()) ;
                return true; }
            echo "." ;
            $t = $t+1;
            sleep(1) ; }
        return null ;
    }

    protected function getSourceFilePath($flag = null){
        if (isset($this->params["source"])) { return $this->params["source"] ; }
        if (isset($flag)) { $question = "Enter $flag source file path" ; }
        else { $question = "Enter source file path"; }
        $input = self::askForInput($question) ;
        return ($input=="") ? false : $input ;
    }

    protected function getTargetFilePath($flag = null){
        if (isset($this->params["target"])) { return $this->params["target"] ; }
        if (isset($flag)) { $question = "Enter $flag target file path" ; }
        else { $question = "Enter target file path"; }
        $input = self::askForInput($question) ;
        return ($input=="") ? false : $input ;
    }

}