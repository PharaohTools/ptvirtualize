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

    public function autoSSHCli() {
        $this->loadFiles();
        // try the connection
        $thisPort = (isset($this->papyrus["port"])) ? : 22 ;
        $sshWorks = $this->waitForSsh($this->papyrus["target"], $thisPort);

        if ($sshWorks == true) {
            $sshParams = $this->params ;
            // try papyrus first. if box specified in virtufile exists there, try its connection details.
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

    }

    // @todo this needs testing
    public function autoSSHData() {
        $this->loadFiles();
        // try the connection
        $thisPort = (isset($this->papyrus["port"])) ? : 22 ;
        $sshWorks = $this->waitForSsh($this->papyrus["target"], $thisPort);

        // @todo need to set the SSH Data we're sending
        if ($sshWorks == true) {
            $sshParams = $this->params ;
            // try papyrus first. if box specified in virtufile exists there, try its connection details.
            $srv = array(
                "user" => $this->papyrus["username"] ,
                "password" => $this->papyrus["password"] ,
                "target" => $this->papyrus["target"] );
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
            $ssh->performInvokeSSHData() ;
            return true ;
        }

    }

    // @todo this and method below can be rolled into one
    public function autoSFTPPut() {
        $this->loadFiles();
        // try the connection
        $thisPort = (isset($this->papyrus["port"])) ? : 22 ;
        $sshWorks = $this->waitForSsh($this->papyrus["target"], $thisPort);

        if ($sshWorks == true) {
            $sftpParams = $this->params ;
            // try papyrus first. if box specified in virtufile exists there, try its connection details.
            $srv = array(
                "user" => $this->papyrus["username"] ,
                "password" => $this->papyrus["password"] ,
                "target" => $this->papyrus["target"] );
            $sftpParams["yes"] = true ;
            $sftpParams["guess"] = true ;
            $sftpParams["servers"] = serialize(array($srv)) ;
            $sftpParams["source"] = $this->getSourceFilePath() ;
            $sftpParams["target"] = $this->getTargetFilePath() ;
            if (isset($this->papyrus["port"])) {
                $srv["port"] =
                    (isset($this->papyrus["port"]))
                        ? $this->papyrus["port"] : 22; }
            if (isset($this->papyrus["timeout"])) {
                $srv["timeout"] = $this->papyrus["timeout"] ; }

            $sshFactory = new \Model\SFTP();
            $ssh = $sshFactory->getModel($sftpParams) ;
            $ssh->performSFTPPut() ;
            return true ;
        }

    }

    // @todo this and method above can be rolled into one
    public function autoSFTPGet() {
        $this->loadFiles();
        // try the connection
        $thisPort = (isset($this->papyrus["port"])) ? : 22 ;
        $sshWorks = $this->waitForSsh($this->papyrus["target"], $thisPort);

        if ($sshWorks == true) {
            $sftpParams = $this->params ;
            // try papyrus first. if box specified in virtufile exists there, try its connection details.
            $srv = array(
                "user" => $this->papyrus["username"] ,
                "password" => $this->papyrus["password"] ,
                "target" => $this->papyrus["target"] );;
            $sftpParams["yes"] = true ;
            $sftpParams["guess"] = true ;
            $sftpParams["servers"] = serialize(array($srv)) ;
            $sftpParams["source"] = $this->getSourceFilePath() ;
            $sftpParams["target"] = $this->getTargetFilePath() ;
            if (isset($this->papyrus["port"])) {
                $srv["port"] =
                    (isset($this->papyrus["port"]))
                        ? $this->papyrus["port"] : 22; }
            if (isset($this->papyrus["timeout"])) {
                $srv["timeout"] = $this->papyrus["timeout"] ; }

            $sshFactory = new \Model\SFTP();
            $ssh = $sshFactory->getModel($sftpParams) ;
            $ssh->performSFTPPut() ;
            return true ;
        }

    }

    protected function loadFiles() {
        $this->virtufile = $this->loadVirtualizeFile();
        $this->papyrus = $this->loadPapyrusLocal();
    }

    protected function loadVirtualizeFile() {
        $prFactory = new \Model\VirtualizeRequired();
        $ptvirtualizeFileLoader = $prFactory->getModel($this->params, "VirtualizeFileLoader") ;
        return $ptvirtualizeFileLoader->load() ;
    }

    protected function loadPapyrusLocal() {
        $prFactory = new \Model\VirtualizeRequired();
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
        $logging->log("Waiting for ssh...") ;
        while ($t < $totalTime) {
            $command = CLEOCOMM." port is-responding --ip=$ip --port-number=$thisPort" ;
            $vmInfo = self::executeAndLoad($command) ;
            if (strpos($vmInfo, "Port: Success") != false) {
                $logging->log("IP $ip and Port $thisPort are responding, we'll use those...") ;
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