<?php

Namespace Model;

class ShellProvision extends BaseShellAllOS {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Provision") ;

    public $virtualizefile;
    public $papyrus ;

    public function provision($provisionerSettings, $osProvisioner) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $shellSpellings = array("shell", "bash");
        if (in_array($provisionerSettings["tool"], $shellSpellings)) {
            $logging->log("Initialising Shell Provision... ") ;
            $init = $this->initialiseShellProvision($provisionerSettings) ;
            return $this->shellProvision($provisionerSettings, $init, $osProvisioner) ; }
        else {
            $logging->log("Unrecognised Shell Provisioning Tool {$provisionerSettings["tool"]} specified") ;
            return null ; }
    }

    // @todo this code is identical to the initialisePharaohTools except the provisionfile extension.
    // @todo they should both extend a base class of provisioner
    protected function initialiseShellProvision($provisionerSettings) {

        if ($provisionerSettings["target"] == "guest") {

            $loggingFactory = new \Model\Logging();
            $logging = $loggingFactory->getModel($this->params);
            // get target ip from virtualizefile if its there
            // if not check for guest additions installed
            $ips = array() ;
            if (isset($this->virtualizefile->config["ssh"]["target"])) {
                $logging->log("Using Virtualizefile defined ssh target of {$this->virtualizefile->config["ssh"]["target"]}... ") ;
                $ips[] = $this->virtualizefile->config["ssh"]["target"] ; }
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

            if (isset($this->virtualizefile->config["ssh"]["port"])) {
                $thisPort = $this->virtualizefile->config["ssh"]["port"] ; }
            else {
                $thisPort = 22 ; }

            $ip = $this->waitForSsh($ips, $thisPort, 2) ;
            if ($ip != null) {
                $chosenIp = $ip ; }

            $encodedBox = serialize(array(array(
                "user" => "{$this->virtualizefile->config["ssh"]["user"]}",
                "password" => "{$this->virtualizefile->config["ssh"]["password"]}",
                "target" => "$chosenIp"
            ))) ;

            $this->storeInPapyrus($this->virtualizefile->config["ssh"]["user"], $this->virtualizefile->config["ssh"]["password"], $chosenIp) ;

            $provisionFile = $this->virtualizefile->config["vm"]["default_tmp_dir"]."provision.sh" ;

            $ray = array() ;
            $ray["provision_file"] = $provisionFile ;
            $ray["encoded_box"] = $encodedBox ;
            $ray["provision"] = $provisionFile ; }
        else {
            $ray = array() ; }
        return $ray ;

    }

    protected function shellProvision($provisioner, $init, $osProvisioner) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        if ($provisioner["target"] == "guest") {
            if (isset($provisioner["default"])) {
                $logging->log("Provisioning VM with Default Shell Script for {$provisioner["default"]}...") ;
                $this->sshProvision($provisioner, $init, $osProvisioner); }
            else {
                $logging->log("Starting Provisioning VM with Shell...") ;
                $logging->log("SFTP Configuration Management .sh file to VM for Shell...") ;
                $this->sftpProvision($provisioner, $init);
                $logging->log("SSH Execute Provisioning VM with Shell script...") ;
                $this->sshProvision($provisioner, $init, $osProvisioner); } }
        else if ($provisioner["target"] == "host") {
            $logging->log("Provisioning Host with Shell...") ;
            $command = "sh {$provisioner["script"]}" ;
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
        if (isset($this->virtualizefile->config["ssh"]["port"])) {
            $sftpParams["port"] = $this->virtualizefile->config["ssh"]["port"] ; }
        if (isset($this->virtualizefile->config["ssh"]["timeout"])) {
            $sftpParams["timeout"] = $this->virtualizefile->config["ssh"]["timeout"] ; }
        $sftpFactory = new \Model\SFTP();
        $sftp = $sftpFactory->getModel($sftpParams) ;
        $sftp->performSFTPPut();
    }

    protected function sshProvision($provisionerSettings, $init, $osProvisioner) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $sshParams = $this->params ;
        if (isset($provisionerSettings["default"])) {
            $logging->log("Attempting to use default shell script {$provisionerSettings["default"]}") ;
            $methodName = "get".ucfirst($provisionerSettings["default"])."SSHData" ;
            if (method_exists($osProvisioner, $methodName)) {
                $logging->log("Found {$provisionerSettings["default"]} method in OS Provisioner") ;
                $sshParams["ssh-data"] = $osProvisioner->$methodName($init["provision_file"]) ; }
            else {
                $logging->log("No method {$provisionerSettings["default"]} found in OS Provisioner, cannot continue") ;
                return false ; } }
        else {
            $logging->log("Attempting to use Standard shell script {$provisionerSettings["default"]}") ;
            $methodName = "getStandardShellSSHData" ;
            if (method_exists($osProvisioner, $methodName)) {
                $logging->log("Found {$provisionerSettings["default"]} method in OS Provisioner") ;
                $sshParams["ssh-data"] = $osProvisioner->$methodName($init["provision_file"]) ; }
            else {
                $logging->log("No method {$provisionerSettings["default"]} found in OS Provisioner, cannot continue") ;
                return false ; } }
        $sshParams["yes"] = true ;
        $sshParams["guess"] = true ;
        $sshParams["servers"] = $init["encoded_box"] ;
        if (isset($this->virtualizefile->config["ssh"]["port"])) {
            $sshParams["port"] = $this->virtualizefile->config["ssh"]["port"] ; }
        if (isset($this->virtualizefile->config["ssh"]["timeout"])) {
            $sshParams["timeout"] = $this->virtualizefile->config["ssh"]["timeout"] ; }
        $sshFactory = new \Model\Invoke();
        $ssh = $sshFactory->getModel($sshParams) ;
        $ssh->performInvokeSSHData() ;
    }

    protected function waitUntilGetIP() {
        $totalTime = (isset($this->virtualizefile->config["vm"]["ip_find_timeout"]))
            ? $this->virtualizefile->config["vm"]["ip_find_timeout"] : 180 ;
        $ips = array() ;
        //while ($t < $totalTime) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $command = VBOXMGCOMM." guestproperty enumerate {$this->virtualizefile->config["vm"]["name"]} " ;
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

        var_dump($outStr) ;
        return $ips ;
    }

    protected function waitForSsh($ips, $thisPort) {
        $t = 0;
        $totalTime = (isset($this->virtualizefile->config["vm"]["ssh_find_timeout"]))
            ? $this->virtualizefile->config["vm"]["ssh_find_timeout"] : 300 ;
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

    protected function storeInPapyrus($user, $pass, $target) {
        $virtualizeBox = array() ;
        $virtualizeBox["name"] = $this->virtualizefile->config["vm"]["name"] ;
        $virtualizeBox["username"] = $user ;
        $virtualizeBox["password"] = $pass ;
        $virtualizeBox["target"] = $target ;
        $virtualizeBox = array_merge($this->papyrus, $virtualizeBox) ;
        \Model\AppConfig::setProjectVariable($this->virtualizefile->config["vm"]["name"], $virtualizeBox, null, null, true) ;
    }

    protected function countNICs() {
        $count = 0;
        for ($i=0; $i<100; $i++) {
            if (isset($this->virtualizefile->config["network"]["nic$i"])) {
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