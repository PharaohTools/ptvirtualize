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

    public $virtufile;
    public $papyrus ;

    public function provision($provisionerSettings, $osProvisioner) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $shellSpellings = array("shell", "bash");
        if (in_array($provisionerSettings["tool"], $shellSpellings)) {
            $logging->log("Initialising Shell Provision... ", $this->getModuleName());
            $init = $this->initialiseShellProvision($provisionerSettings) ;
            return $this->shellProvision($provisionerSettings, $init, $osProvisioner) ; }
        else {
            $logging->log("Unrecognised Shell Provisioning Tool {$provisionerSettings["tool"]} specified", $this->getModuleName(), LOG_FAILURE_EXIT_CODE);
            return false ; }
    }

    // @todo this code is identical to the initialisePharaohTools except the provisionfile extension.
    // @todo they should both extend a base class of provisioner
    protected function initialiseShellProvision($provisionerSettings) {

        if ($provisionerSettings["target"] == "guest") {
            $loggingFactory = new \Model\Logging();
            $logging = $loggingFactory->getModel($this->params);
            // get target ip from virtufile if its there
            // if not check for guest additions installed
            $ips = array() ;
            if (isset($this->virtufile->config["ssh"]["target"])) {
                $logging->log("Using Virtufile defined ssh target of {$this->virtufile->config["ssh"]["target"]}... ", $this->getModuleName());
                $ips[] = $this->virtufile->config["ssh"]["target"] ; }
            else if ($this->checkForGuestAdditions()==true) {
                $logging->log("Guest additions found on VM, finding target from it...", $this->getModuleName());
                $wug = $this->waitUntilGetIP() ;
                $ips = array_merge($wug, $ips) ;
                $ipstring = implode(", " , $ips) ;
                $logging->log("... Found $ipstring", $this->getModuleName()); }
            else {
                $gdi = $this->getDefaultIpList() ;
                $ips = array_merge($ips, $gdi) ;
                $logging->log("Using default ip list of $gdi", $this->getModuleName());  }
            if (isset($this->virtufile->config["ssh"]["port"])) {
                $thisPort = $this->virtufile->config["ssh"]["port"] ; }
            else {
                $thisPort = 22 ; }
            $ip = $this->waitForSsh($ips, $thisPort, 2) ;
            if ($ip != null) { $chosenIp = $ip ; }
            $encodedBox = serialize(array(array(
                "user" => "{$this->virtufile->config["ssh"]["user"]}",
                "password" => "{$this->virtufile->config["ssh"]["password"]}",
                "target" => "$chosenIp",
                "driver" => "{$this->virtufile->config["ssh"]["driver"]}",
                "port" => $thisPort,
                "timeout" => $this->virtufile->config["ssh"]["timeout"]
            ))) ;
            $this->storeInPapyrus($this->virtufile->config["ssh"]["user"], $this->virtufile->config["ssh"]["password"], $chosenIp) ;
            $provisionFile = $this->virtufile->config["vm"]["default_tmp_dir"]."provision.sh" ;
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

//        var_dump('in shell provision', $provisioner) ;
        if (isset($provisioner["target"]) && $provisioner["target"] == "guest") {
            if (isset($provisioner["default"])) {
                $logging->log("Provisioning VM with Default Shell Script for {$provisioner["default"]}...", $this->getModuleName());
//                return $this->keyboardProvision($provisioner, $init, $osProvisioner);
                return $this->sshProvision($provisioner, $init, $osProvisioner);

            }
            else if (isset($provisioner["target"]) &&
                     $provisioner["target"]=="guest" &&
                     isset($provisioner["script"])) {
                $logging->log("Provisioning Guest with local Shell Script {$provisioner["script"]}...", $this->getModuleName()) ;
                $init["provision_file"] = $provisioner["script"] ;
//                return $this->keyboardProvision($provisioner, $init, $osProvisioner);
                return $this->sshProvision($provisioner, $init, $osProvisioner);

            }
            else if (isset($provisioner["target"]) &&
                     $provisioner["target"]=="guest" &&
                     isset($provisioner["data"])) {
                $logging->log("Provisioning Guest with Shell Data...", $this->getModuleName()) ;
                $init["provision_file"] = $provisioner["data"] ;
//                return $this->keyboardProvision($provisioner, $init, $osProvisioner);
                return $this->sshProvision($provisioner, $init, $osProvisioner);

            }
            else {
                $logging->log("Starting Provisioning VM with Shell...", $this->getModuleName());
                $logging->log("SFTP shell script file to VM for Shell...", $this->getModuleName());
                $this->sftpProvision($provisioner, $init);
                $logging->log("SSH Execute Provisioning VM with Shell script...", $this->getModuleName());
//                return $this->keyboardProvision($provisioner, $init, $osProvisioner);
                return $this->sshProvision($provisioner, $init, $osProvisioner);

            } }
        else if ($provisioner["target"] == "host") {
            if (isset($provisioner["data"])) {
                $logging->log("Provisioning Host with Shell Data...", $this->getModuleName());
                system($provisioner["data"], $exit_code) ;
                return ($exit_code === 0) ? true : false ;
//                "{$provisioner["script"]}" ;
            } else if (isset($provisioner["script"])) {
                $logging->log("Provisioning Host with Shell Script...", $this->getModuleName());
                $command = "sh {$provisioner["script"]}" ;
                return self::executeAndOutput($command) ;
            } else {
                $logging->log("Provisioning Host requires either Shell Script or Shell Data...", $this->getModuleName());
                return false ;
            } }
        return true ;
    }

    protected function sftpProvision($provisionerSettings, $init) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $sftpParams = $this->params ;
        $sftpParams["yes"] = true ;
        $sftpParams["guess"] = true ;
        $sftpParams["servers"] = $init["encoded_box"] ;
//        var_dump($provisionerSettings) ;
        $sftpParams["source"] = $provisionerSettings["source"] ;
        $sftpParams["target"] = $init["provision_file"] ;
        if (isset($this->virtufile->config["ssh"]["port"])) {
            $sftpParams["port"] = $this->virtufile->config["ssh"]["port"] ; }
        if (isset($this->virtufile->config["ssh"]["timeout"])) {
            $sftpParams["timeout"] = $this->virtufile->config["ssh"]["timeout"] ; }
        if (isset($this->virtufile->config["ssh"]["retries"])) {
            $sftpParams["retries"] = $this->virtufile->config["ssh"]["retries"] ; }
        if (isset($this->virtufile->config["ssh"]["interval"])) {
            $sftpParams["interval"] = $this->virtufile->config["ssh"]["interval"] ; }
        $sftpParams["driver"] = $this->virtufile->config["ssh"]["driver"] ;
        $sftpFactory = new \Model\SFTP();
        $sftp = $sftpFactory->getModel($sftpParams) ;
        $res = $sftp->performSFTPPut();
        if ($res == false) {  $logging->log("Provisioning Shell SFTP Failed...", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ; }
        return ($res == true) ? true : false ;

    }

    protected function sshProvision($provisionerSettings, $init, $osProvisioner) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $sshParams = $this->params ;
        if (isset($provisionerSettings["default"])) {
            $logging->log("Attempting to use default shell script {$provisionerSettings["default"]}", $this->getModuleName());
            $methodName = "get".ucfirst($provisionerSettings["default"])."SSHData" ;
            if (method_exists($osProvisioner, $methodName)) {
                $logging->log("Found {$provisionerSettings["default"]} method in OS Provisioner", $this->getModuleName());
//                var_dump('init is', $init);
//                var_dump('pset is', $provisionerSettings);
                $sshParams["ssh-data"] = $osProvisioner->$methodName($init["provision_file"], $provisionerSettings) ; }
            else {
                $logging->log("No method {$provisionerSettings["default"]} found in OS Provisioner, cannot continue", $this->getModuleName(), LOG_FAILURE_EXIT_CODE);
                return false ; } }
        else {
            $logging->log("Attempting to use Standard shell script {$init["provision_file"]}", $this->getModuleName());
            $methodName = "getStandardShellSSHData" ;
            if (method_exists($osProvisioner, $methodName)) {
                $logging->log("Found {$methodName} method in OS Provisioner", $this->getModuleName());
                $sshParams["ssh-data"] = $osProvisioner->$methodName($init["provision_file"]) ; }
            else {
                $logging->log("No method {$methodName} found in OS Provisioner, cannot continue", $this->getModuleName(), LOG_FAILURE_EXIT_CODE);
                return false ; } }
        $sshParams["yes"] = true ;
        $sshParams["guess"] = true ;
        $sshParams["servers"] = $init["encoded_box"] ;
        $sshParams["driver"] = $this->virtufile->config["ssh"]["driver"] ;
        if (isset($this->virtufile->config["ssh"]["port"])) {
            $sshParams["port"] = $this->virtufile->config["ssh"]["port"] ; }
        if (isset($this->virtufile->config["ssh"]["timeout"])) {
            $sshParams["timeout"] = $this->virtufile->config["ssh"]["timeout"] ; }
        if (isset($this->virtufile->config["ssh"]["retries"])) {
            $sshParams["retries"] = $this->virtufile->config["ssh"]["retries"] ; }
        if (isset($this->virtufile->config["ssh"]["interval"])) {
            $sshParams["interval"] = $this->virtufile->config["ssh"]["interval"] ; }
        $sshFactory = new \Model\Invoke();
        $ssh = $sshFactory->getModel($sshParams) ;
        $res = $ssh->performInvokeSSHData() ;
        if ($res == false) {  $logging->log("Provisioning Shell SSH Failed...", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ; }
        return ($res == true) ? true : false ;
    }


    protected function keyboardProvision($provisionerSettings, $osProvisioner) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $sshParams = $this->params ;
        if (isset($provisionerSettings["default"])) {
            $logging->log("Attempting to use default shell script {$provisionerSettings["default"]}", $this->getModuleName());
            $methodName = "get".ucfirst($provisionerSettings["default"])."SSHData" ;
            if (method_exists($osProvisioner, $methodName)) {
                $logging->log("Found {$provisionerSettings["default"]} method in OS Provisioner", $this->getModuleName());
//                var_dump('init is', $init);
//                var_dump('pset is', $provisionerSettings);
                $sshParams["ssh-data"] = $osProvisioner->$methodName($init["provision_file"], $provisionerSettings) ; }
            else {
                $logging->log("No method {$provisionerSettings["default"]} found in OS Provisioner, cannot continue", $this->getModuleName(), LOG_FAILURE_EXIT_CODE);
                return false ; } }
        else {
            $logging->log("Attempting to use Standard shell script {$init["provision_file"]}", $this->getModuleName());
            $methodName = "getStandardShellSSHData" ;
            if (method_exists($osProvisioner, $methodName)) {
                $logging->log("Found {$methodName} method in OS Provisioner", $this->getModuleName());
                $sshParams["ssh-data"] = $osProvisioner->$methodName($init["provision_file"]) ; }
            else {
                $logging->log("No method {$methodName} found in OS Provisioner, cannot continue", $this->getModuleName(), LOG_FAILURE_EXIT_CODE);
                return false ; } }
        $sshParams["yes"] = true ;
        $sshParams["guess"] = true ;
//        $sshParams["servers"] = $init["encoded_box"] ;
//        $sshParams["driver"] = $this->virtufile->config["ssh"]["driver"] ;
//        if (isset($this->virtufile->config["ssh"]["port"])) {
//            $sshParams["port"] = $this->virtufile->config["ssh"]["port"] ; }
//        if (isset($this->virtufile->config["ssh"]["timeout"])) {
//            $sshParams["timeout"] = $this->virtufile->config["ssh"]["timeout"] ; }
//        if (isset($this->virtufile->config["ssh"]["retries"])) {
//            $sshParams["retries"] = $this->virtufile->config["ssh"]["retries"] ; }
//        if (isset($this->virtufile->config["ssh"]["interval"])) {
//            $sshParams["interval"] = $this->virtufile->config["ssh"]["interval"] ; }
        $vkFactory = new \Model\VirtualKeyboard();
        $vk = $vkFactory->getModel($sshParams) ;
        $res = $vk->virtualKeyboardProvision($provisionerSettings, $osProvisioner) ;
        if ($res == false) {  $logging->log("Provisioning Shell via Keyboard Failed...", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ; }
        return ($res == true) ? true : false ;
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
                        $logging->log("Found $ip...", $this->getModuleName());
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
        $logging->log("Waiting for ssh...", $this->getModuleName());
        while ($t < $totalTime) {
            foreach ($ips as $ip) {
                $command = PTCCOMM." port is-responding --ip=$ip --port-number=$thisPort" ;
                $vmInfo = self::executeAndLoad($command) ;
                if (strpos($vmInfo, "Port: Success") != false) {
                    $logging->log("IP $ip and Port $thisPort are responding, we'll use those...", $this->getModuleName());
                    return $ip ; }
                echo "." ;
                $t = $t+1; }
            sleep(1) ; }
        echo "\n" ;
        return null ;
    }

    protected function storeInPapyrus($user, $pass, $target) {
        $ptvirtualizeBox = array() ;
        $ptvirtualizeBox["name"] = $this->virtufile->config["vm"]["name"] ;
        $ptvirtualizeBox["username"] = $user ;
        $ptvirtualizeBox["password"] = $pass ;
        $ptvirtualizeBox["target"] = $target ;
        $ptvirtualizeBox["driver"] = $this->virtufile->config["ssh"]["driver"] ;
        if (is_array($this->papyrus)) { $ptvirtualizeBox = array_merge($this->papyrus, $ptvirtualizeBox) ; }
        \Model\AppConfig::setProjectVariable($this->virtufile->config["vm"]["name"], $ptvirtualizeBox, null, null, true) ;
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