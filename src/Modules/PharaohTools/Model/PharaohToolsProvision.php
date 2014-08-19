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

    public $phlagrantfile;
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
            $logging->log("Unrecognised Pharoah Provisioning Tool {$provisionerSettings["tool"]} specified") ;
            return null ; }
    }

    protected function initialisePharaohProvision($provisionerSettings) {

        if ($provisionerSettings["target"] == "guest") {
            $loggingFactory = new \Model\Logging();
            $logging = $loggingFactory->getModel($this->params);
            // get target ip from phlagrantfile if its there
            // if not check for guest additions installed
            $ips = array() ;
            if (isset($this->phlagrantfile->config["ssh"]["target"])) {
                $logging->log("Using Phlagrantfile defined ssh target of {$this->phlagrantfile->config["ssh"]["target"]}... ") ;
                $ips[] = $this->phlagrantfile->config["ssh"]["target"] ; }
            else if ($this->checkForGuestAdditions()==true) {
                $logging->log("Guest additions found on VM, finding target from it...") ;
                $wug = $this->waitUntilGetIP() ;
                $ips = array_merge($wug, $ips) ;
                $ipstring = implode(", " , $ips) ;
                $logging->log("... Found $ipstring") ; }
            else {
                $gdi = $this->getDefaultIpList() ;
                $ips = array_merge($ips, $gdi) ;
                $logging->log("Using default ip list of $gdi") ;  }

            if (isset($this->phlagrantfile->config["ssh"]["port"])) {
                $thisPort = $this->phlagrantfile->config["ssh"]["port"] ; }
            else {
                $thisPort = 22 ; }

            $ip = $this->waitForSsh($ips, $thisPort, 2) ;
            if ($ip != null) {
                $chosenIp = $ip ; }

            $encodedBox = serialize(array(array(
                "user" => "{$this->phlagrantfile->config["ssh"]["user"]}",
                "password" => "{$this->phlagrantfile->config["ssh"]["password"]}",
                "target" => "$chosenIp"
            ))) ;

            $this->storeInPapyrus($this->phlagrantfile->config["ssh"]["user"], $this->phlagrantfile->config["ssh"]["password"], $chosenIp) ;

            $provisionFile = $this->phlagrantfile->config["vm"]["default_tmp_dir"]."provision.php" ;

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
        if (isset($this->phlagrantfile->config["ssh"]["port"])) {
            $sftpParams["port"] = $this->phlagrantfile->config["ssh"]["port"] ; }
        if (isset($this->phlagrantfile->config["ssh"]["timeout"])) {
            $sftpParams["timeout"] = $this->phlagrantfile->config["ssh"]["timeout"] ; }
        $sftpFactory = new \Model\SFTP();
        $sftp = $sftpFactory->getModel($sftpParams) ;
        $sftp->performSFTPPut();
    }

    protected function sshProvision($provisionerSettings, $init, $osProvisioner) {

        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $sshParams = $this->params ;
        if (isset($provisionerSettings["default"])) {
            $logging->log("Attempting to use default {$provisionerSettings["tool"]} script {$provisionerSettings["default"]}") ;
            $methodName = "get".ucfirst($provisionerSettings["default"])."SSHData" ;
            if (method_exists($osProvisioner, $methodName)) {
                $logging->log("Found {$provisionerSettings["default"]} method in OS Provisioner") ;
                $sshParams["ssh-data"] = $osProvisioner->$methodName($init["provision_file"]) ; }
            else {
                $logging->log("No method {$provisionerSettings["default"]} found in OS Provisioner, cannot continue") ;
                return false ; } }
        else {
            $tool = ucfirst($provisionerSettings["tool"]) ;
            $logging->log("Attempting to use Standard {$tool} script {$provisionerSettings["script"]}") ;
            $methodName = "getStandard{$tool}SSHData" ;
            if (method_exists($osProvisioner, $methodName)) {
                $logging->log("Found {$provisionerSettings["default"]} method in OS Provisioner") ;
                $sshParams["ssh-data"] = $osProvisioner->$methodName($init["provision_file"]) ; }
            else {
                $logging->log("No method {$provisionerSettings["default"]} found in OS Provisioner, cannot continue") ;
                return false ; } }

        $sshParams["yes"] = true ;
        $sshParams["guess"] = true ;
        $sshParams["servers"] = $init["encoded_box"] ;
        if (isset($this->phlagrantfile->config["ssh"]["port"])) {
            $sshParams["port"] = $this->phlagrantfile->config["ssh"]["port"] ; }
        if (isset($this->phlagrantfile->config["ssh"]["timeout"])) {
            $sshParams["timeout"] = $this->phlagrantfile->config["ssh"]["timeout"] ; }
        $sshFactory = new \Model\Invoke();
        $ssh = $sshFactory->getModel($sshParams) ;
        $ssh->performInvokeSSHData() ;
    }

    protected function waitUntilGetIP() {
        $totalTime = (isset($this->phlagrantfile->config["vm"]["ip_find_timeout"]))
            ? $this->phlagrantfile->config["vm"]["ip_find_timeout"] : 180 ;
        $ips = array() ;
        //while ($t < $totalTime) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $command = "vboxmanage guestproperty enumerate {$this->phlagrantfile->config["vm"]["name"]} | grep \"V4/IP\" " ;
        $cards = $this->countNICs() ;
        for ($secs = 0; $secs<$totalTime; $secs++) {
            $vmInfo = self::executeAndLoad($command) ;
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
        $totalTime = (isset($this->phlagrantfile->config["vm"]["ssh_find_timeout"]))
            ? $this->phlagrantfile->config["vm"]["ssh_find_timeout"] : 300 ;
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $logging->log("Waiting for ssh...") ;
        while ($t < $totalTime) {
            foreach ($ips as $ip) {
                $command = "cleopatra port is-responding --ip=$ip --port-number=$thisPort" ;
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

    protected function storeInPapyrus($user, $pass, $target) {
        $phlagrantBox = array() ;
        $phlagrantBox["name"] = $this->phlagrantfile->config["vm"]["name"] ;
        $phlagrantBox["username"] = $user ;
        $phlagrantBox["password"] = $pass ;
        $phlagrantBox["target"] = $target ;
        $phlagrantBox = array_merge($this->papyrus, $phlagrantBox) ;
        \Model\AppConfig::setProjectVariable($this->phlagrantfile->config["vm"]["name"], $phlagrantBox, null, null, true) ;
    }

    protected function countNICs() {
        $count = 0;
        for ($i=0; $i<100; $i++) {
            if (isset($this->phlagrantfile->config["network"]["nic$i"])) {
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