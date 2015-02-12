<?php

Namespace Model;

class PharaohToolsProvision extends BasePharaohToolsAllOS {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Provision") ;

    public $virtufile;
    public $papyrus ;

    public function provision($provisionerSettings, $osProvisioner) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $cleoSpellings = array("Cleopatra", "cleopatra", "Cleo", "cleo") ;
        $dapperSpellings = array("Dapperstrano", "dapperstrano", "dapper", "Dapper" ) ;
        if (in_array($provisionerSettings["tool"], $cleoSpellings)) {
            $logging->log("Initialising Pharaoh Cleopatra Provision... ") ;
            $init = $this->initialisePharaohProvision($provisionerSettings, $osProvisioner) ;
            return $this->cleopatraProvision($provisionerSettings, $init, $osProvisioner) ; }
        else if (in_array($provisionerSettings["tool"], $dapperSpellings)) {
            $logging->log("Initialising Pharaoh Dapperstrano Provision... ") ;
            $init = $this->initialisePharaohProvision($provisionerSettings, $osProvisioner) ;
            return $this->dapperstranoProvision($provisionerSettings, $init, $osProvisioner) ; }
        else {
            $logging->log("Unrecognised Pharaoh Provisioning Tool {$provisionerSettings["tool"]} specified") ;
            return null ; }
    }

    protected function initialisePharaohProvision($provisionerSettings) {

        if ($provisionerSettings["target"] == "guest") {
            $loggingFactory = new \Model\Logging();
            $logging = $loggingFactory->getModel($this->params);
            // get target ip from virtufile if its there
            // if not check for guest additions installed
            $pflocal = $this->loadPapyrusLocal() ;

            $ips = array() ;
            if (isset($pflocal[$this->virtufile->config["vm"]["name"]]["target"])) {
                $logging->log("Using papyrusfilelocal defined ssh target of {$pflocal[$this->virtufile->config["vm"]["name"]]["target"]}... ") ;
                $ips[] = $pflocal[$this->virtufile->config["vm"]["name"]]["target"] ; }
            else if (isset($this->virtufile->config["ssh"]["target"])) {
                $logging->log("Using Virtufile defined ssh target of {$this->virtufile->config["ssh"]["target"]}... ") ;
                $ips[] = $this->virtufile->config["ssh"]["target"] ; }
            else if ($this->checkForGuestAdditions()==true) {
                $logging->log("Guest additions found on VM, finding target from it...") ;
                $wug = $this->waitUntilGetIP() ;
                $ips = array_merge($wug, $ips) ;
                // this should be quicker because guest additions returns the unused one first
                $ips = array_reverse($ips) ;
                $ipstring = implode(", " , $ips) ;
                $logging->log("... Found $ipstring") ; }
            else {
                $gdi = $this->getDefaultIpList() ;
                $ips = array_merge($ips, $gdi) ;
                $logging->log("Using default ip list of $gdi") ;  }

            if (isset($this->virtufile->config["ssh"]["port"])) {
                $thisPort = $this->virtufile->config["ssh"]["port"] ; }
            else {
                $thisPort = 22 ; }

            $ip = $this->waitForSsh($ips, $thisPort, 2) ;
            if ($ip != null) {
                $chosenIp = $ip ; }

            $encodedBox = serialize(array(array(
                "user" => "{$this->virtufile->config["ssh"]["user"]}",
                "password" => "{$this->virtufile->config["ssh"]["password"]}",
                "target" => "$chosenIp"
            ))) ;

            $this->storeInPapyrus($this->virtufile->config["ssh"]["user"], $this->virtufile->config["ssh"]["password"], $chosenIp) ;

            $provisionFile = $this->virtufile->config["vm"]["default_tmp_dir"]."provision.php" ;

            $ray = array() ;
            $ray["provision_file"] = $provisionFile ;
            $ray["encoded_box"] = $encodedBox ;
            $ray["provision"] = $provisionFile ; }
        else {
            $ray = array() ; }
        return $ray ;
    }

    protected function cleopatraProvision($provisionerSettings, $init, $osProvisioner) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        if ($provisionerSettings["target"] == "guest") {
            if (isset($provisioner["default"])) {
                $logging->log("Provisioning VM with Default Cleopatra Autopilot for {$provisioner["default"]}...") ;
                $this->sshProvision($provisioner, $init, $osProvisioner); }
            else {
                $logging->log("Starting Provisioning VM with Cleopatra...") ;
                $logging->log("SFTP Configuration Management Autopilot to VM for Cleopatra...") ;
                $this->sftpProvision($provisionerSettings, $init);
                $logging->log("SSH Execute Provisioning VM with Cleopatra...") ;
                $this->sshProvision($provisionerSettings, $init, $osProvisioner); } }
        else if ($provisionerSettings["target"] == "host") {
            $logging->log("Provisioning Host with Cleopatra...") ;
            $command = "cleopatra auto x --af={$provisionerSettings["script"]}" ;
            var_dump("comm", $command) ;
            if (isset($provisionerSettings["params"])) {
                foreach ($provisionerSettings["params"] as $paramkey => $paramval) {
                    $command .= " --$paramkey=\"$paramval\"" ; } }
            self::executeAndOutput($command) ; }
        return true ;
    }

    protected function dapperstranoProvision($provisionerSettings, $init, $osProvisioner) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        if ($provisionerSettings["target"] == "guest") {
            if (isset($provisioner["default"])) {
                $logging->log("Provisioning VM with Default Cleopatra Autopilot for {$provisioner["default"]}...") ;
                $this->sshProvision($provisioner, $init, $osProvisioner); }
            else {
                $logging->log("Starting Provisioning VM with Dapperstrano...") ;
                $logging->log("SFTP Application Deployment Autopilot for Dapperstrano...") ;
                $this->sftpProvision($provisionerSettings, $init);
                $logging->log("SSH Execute Provisioning VM with Dapperstrano...") ;
                $this->sshProvision($provisionerSettings, $init, $osProvisioner); } }
        else if ($provisionerSettings["target"] == "host") {
            $logging->log("Provisioning Host with Dapperstrano...") ;
            $command = "dapperstrano auto x --af={$provisionerSettings["script"]}" ;
            if (isset($provisionerSettings["params"])) {
                foreach ($provisionerSettings["params"] as $paramkey => $paramval) {
                    $command .= " --$paramkey=\"$paramval\"" ; } }
            self::executeAndOutput($command) ; }
        return true ;
    }

    protected function sftpProvision($provisionerSettings, $init) {
        $sftpParams = $this->params ;
        $sftpParams["yes"] = true ;
        $sftpParams["guess"] = true ;
        $sftpParams["servers"] = $init["encoded_box"] ;
        $sftpParams["source"] = $provisionerSettings["script"] ;
        $sftpParams["target"] = $init["provision_file"] ;
        if (isset($this->virtufile->config["ssh"]["port"])) {
            $sftpParams["port"] = $this->virtufile->config["ssh"]["port"] ; }
        if (isset($this->virtufile->config["ssh"]["timeout"])) {
            $sftpParams["timeout"] = $this->virtufile->config["ssh"]["timeout"] ; }
        $sftpFactory = new \Model\SFTP();
        $sftp = $sftpFactory->getModel($sftpParams) ;
        $sftp->performSFTPPut();
    }

    protected function sshProvision($provisionerSettings, $init, $osProvisioner) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $sshParams = $this->params ;
        $psparams = (isset($provisionerSettings["params"])) ? $provisionerSettings["params"] : array() ;

        if (isset($provisionerSettings["default"])) {
            $logging->log("Attempting to use default {$provisionerSettings["tool"]} script {$provisionerSettings["default"]}") ;
            $methodName = "get".ucfirst($provisionerSettings["default"])."SSHData" ;
            if (method_exists($osProvisioner, $methodName)) {
                $logging->log("Found $methodName method in OS Provisioner") ;
                $sshParams["ssh-data"] = $osProvisioner->$methodName($init["provision_file"], $psparams) ; }
            else {
                $logging->log("No method $methodName found in OS Provisioner, cannot continue") ;
                return false ; } }
        else {
            $tool = ucfirst($provisionerSettings["tool"]) ;
            $logging->log("Attempting to use {$tool} script {$provisionerSettings["script"]}") ;
            $methodName = "getStandard{$tool}SSHData" ;
            if (method_exists($osProvisioner, $methodName)) {
                $logging->log("Found $methodName method in OS Provisioner") ;
                $sshParams["ssh-data"] = $osProvisioner->$methodName($init["provision_file"], $psparams ) ; }
            else {
                $logging->log("No method $methodName found in OS Provisioner, cannot continue") ;
                return false ; } }

        $sshParams["yes"] = true ;
        $sshParams["guess"] = true ;
        $sshParams["servers"] = $init["encoded_box"] ;
        if (isset($this->virtufile->config["ssh"]["port"])) {
            $sshParams["port"] = $this->virtufile->config["ssh"]["port"] ; }
        if (isset($this->virtufile->config["ssh"]["timeout"])) {
            $sshParams["timeout"] = $this->virtufile->config["ssh"]["timeout"] ; }
        $sshFactory = new \Model\Invoke();
        $ssh = $sshFactory->getModel($sshParams) ;
        $ssh->performInvokeSSHData() ;
    }

    protected function waitUntilGetIP() {
        $totalTime = (isset($this->virtufile->config["vm"]["ip_find_timeout"]))
            ? $this->virtufile->config["vm"]["ip_find_timeout"] : 180 ;
        $ips = array() ;
        //while ($t < $totalTime) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $command = VBOXMGCOMM." guestproperty enumerate {$this->virtufile->config["vm"]["name"]} " ;
        $cards = $this->countNICs() ;
        for ($secs = 0; $secs<$totalTime; $secs++) {

            $out = $this->executeAndLoad($command);
            $outLines = explode("\n", $out);
            $outStr = "" ;
            foreach ($outLines as $outLine) {
                if (strpos($outLine, "V4/IP") !== false) {
                    $outStr .= $outLine."\n" ; } }

            $vmInfo = $outStr;
            for ($i=0;$i<30;$i++) { //for up to 30 ifaces
                $pattern = "/VirtualBox/GuestInfo/Net/$i/V4/IP" ;
                $sp = strpos($vmInfo, $pattern) ;
                if ($sp != false) {
                    $afterValue = substr($vmInfo, $sp+strlen($pattern)+9, 27) ;
                    $endOfIp = strpos($afterValue, ",") ;
                    $ip = substr($afterValue, 0, $endOfIp) ;
                    if (!in_array($ip, $ips)) {
                        $ips[] = $ip ;
                        $logging->log("Found $ip...") ;
                        if ($cards==count($ips)) { return $ips ; } } } }
            echo "." ;
            sleep(1) ; }
        echo "\n" ;

        return $ips ;
    }

    protected function waitForSsh($ips, $thisPort) {
        $t = 0;
        $totalTime = (isset($this->virtufile->config["vm"]["ssh_find_timeout"]))
            ? $this->virtufile->config["vm"]["ssh_find_timeout"] : 300 ;
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $logging->log("Waiting for ssh...") ;
        while ($t < $totalTime) {
            foreach ($ips as $ip) {
                $command = CLEOCOMM." port is-responding --ip=$ip --port-number=$thisPort" ;
                $vmInfo = self::executeAndLoad($command) ;
                if (strpos($vmInfo, "Port: Success") != false) {
                    $logging->log("IP $ip and Port $thisPort are responding, we'll use those...") ;
                    return $ip ; }
                echo "." ;
                $t = $t+1; }
            sleep(1) ; }
        echo "\n" ;
        return null ;
    }

    protected function loadPapyrusLocal() {
        $prFactory = new \Model\VirtualizeRequired() ;
        $papyrusLocalLoader = $prFactory->getModel($this->params, "PapyrusLocalLoader") ;
        return $papyrusLocalLoader->load($this->virtufile) ;
    }

    protected function storeInPapyrus($user, $pass, $target) {
        $virtualizeBox = array() ;
        $virtualizeBox["name"] = $this->virtufile->config["vm"]["name"] ;
        $virtualizeBox["username"] = $user ;
        $virtualizeBox["password"] = $pass ;
        $virtualizeBox["target"] = $target ;
        $virtualizeBox = array_merge($this->papyrus, $virtualizeBox) ;
        \Model\AppConfig::setProjectVariable($this->virtufile->config["vm"]["name"], $virtualizeBox, null, null, true) ;
    }

    protected function countNICs() {
        $count = 0;
        for ($i=0; $i<100; $i++) {
            if (isset($this->virtufile->config["network"]["nic$i"])) {
                $count++ ; } }
        return $count ;
    }

    protected function getDefaultIp() {
        return array("10.0.2.15", "192.168.56.101" ) ;
    }

    // @todo ahem
    protected function checkForGuestAdditions() {
        return true ;
    }

}