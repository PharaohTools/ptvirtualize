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
        $ptconfigureSpellings = array("PTConfigure", "ptconfigure", "configure", "Configure") ;
        $ptdeploySpellings = array("PTDeploy", "ptdeploy", "deploy", "Deploy" ) ;
        if (in_array($provisionerSettings["tool"], $ptconfigureSpellings)) {
            $logging->log("Initialising Pharaoh Configure Provision... ", $this->getModuleName()) ;
            $init = $this->initialisePharaohProvision($provisionerSettings, $osProvisioner) ;
            return $this->ptconfigureProvision($provisionerSettings, $init, $osProvisioner) ; }
        else if (in_array($provisionerSettings["tool"], $ptdeploySpellings)) {
            $logging->log("Initialising Pharaoh Deploy Provision... ", $this->getModuleName()) ;
            $init = $this->initialisePharaohProvision($provisionerSettings, $osProvisioner) ;
            $res = $this->ptdeployProvision($provisionerSettings, $init, $osProvisioner) ;
            return $res ; }
        else {
            $logging->log("Unrecognised Pharaoh Provisioning Tool {$provisionerSettings["tool"]} specified", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
            return false ; }
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
                $logging->log("Using papyrusfilelocal defined ssh target of {$pflocal[$this->virtufile->config["vm"]["name"]]["target"]}... ", $this->getModuleName()) ;
                $ips[] = $pflocal[$this->virtufile->config["vm"]["name"]]["target"] ; }
            else if (isset($this->virtufile->config["ssh"]["target"])) {
                $logging->log("Using Virtufile defined ssh target of {$this->virtufile->config["ssh"]["target"]}... ", $this->getModuleName()) ;
                $ips[] = $this->virtufile->config["ssh"]["target"] ; }
            else if ($this->checkForGuestAdditions()==true) {
                $logging->log("Guest additions found on VM, finding target from it...", $this->getModuleName()) ;
                $wug = $this->waitUntilGetIP() ;
                $ips = array_merge($wug, $ips) ;
                // this should be quicker because guest additions returns the unused one first
                $ips = array_reverse($ips) ;
                $ipstring = implode(", " , $ips) ;
                $logging->log("... Found $ipstring", $this->getModuleName()) ; }
            else {
                $gdi = $this->getDefaultIpList() ;
                $ips = array_merge($ips, $gdi) ;
                $logging->log("Using default ip list of $gdi", $this->getModuleName()) ;  }

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
                "target" => "$chosenIp",
                "driver" => "{$this->virtufile->config["ssh"]["driver"]}",
                "port" => $thisPort ,
                "timeout" => $this->virtufile->config["ssh"]["timeout"]
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

    // @todo this and the above method should be one
    protected function ptconfigureProvision($provisionerSettings, $init, $osProvisioner) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        if (!isset($provisionerSettings["source"])) {
            $provisionerSettings["source"] = $provisionerSettings["target"] ; }
        if ($provisionerSettings["target"] == "guest") {
            $logging->log("Starting Provisioning Guest with Pharaoh Configure...", $this->getModuleName()) ;
            if (isset($provisionerSettings["default"])) {
                $logging->log("Provisioning VM with Default Pharaoh Configure Autopilot for {$provisionerSettings["default"]}...", $this->getModuleName()) ;
                return $this->sshProvision($provisionerSettings, $init, $osProvisioner); }
            else if (isset($provisionerSettings["source"]) && $provisionerSettings["source"]=="guest") {
                $logging->log("Provisioning Guest with local Pharaoh Configure Autopilot {$provisionerSettings["script"]}...", $this->getModuleName()) ;
                $init["provision_file"] = $provisionerSettings["script"] ;
                return $this->sshProvision($provisionerSettings, $init, $osProvisioner); }
            else {
                $logging->log("SFTP Application Configuration Autopilot for PTConfigure...", $this->getModuleName()) ;
                $init["provision_file"] = $provisionerSettings["script"] ;
                $this->sftpProvision($provisionerSettings, $init);
                $logging->log("SSH Execute Provisioning Guest with Pharaoh Configure...", $this->getModuleName()) ;
                return $this->sshProvision($provisionerSettings, $init, $osProvisioner); } }
        else if ($provisionerSettings["target"] == "host") {
            $logging->log("Provisioning Host with PTConfigure Starting...", $this->getModuleName()) ;
            $sys = new \Model\SystemDetectionAllOS();
            $prefix = (!in_array($sys->os, array("Windows", "WINNT"))) ? "sudo " : "" ;
            $command = $prefix.PTCCOMM.' auto x --af="'.$provisionerSettings["script"].'"' ;
            if (isset($provisionerSettings["params"])) {
                foreach ($provisionerSettings["params"] as $paramkey => $paramval) {
                    $command .= " --$paramkey=\"$paramval\"" ; } }
            $rc = self::executeAndGetReturnCode($command, true, false) ;
            $logging->log("Provisioning Host with Pharaoh Configure Complete...", $this->getModuleName()) ;
            if ($rc["rc"]!==0) {
                $logging->log("Provisioning Host with Pharaoh Configure Failed...", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
                return false ; }
            return true ; }
    }

    // @todo this and the above method should be one
    protected function ptdeployProvision($provisionerSettings, $init, $osProvisioner) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        if (!isset($provisionerSettings["source"])) {
            $provisionerSettings["source"] = $provisionerSettings["target"] ; }
        if ($provisionerSettings["target"] == "guest") {
            $logging->log("Starting Provisioning Guest with Pharaoh Deploy...", $this->getModuleName()) ;
            if (isset($provisionerSettings["default"])) {
                $logging->log("Provisioning VM with Default Pharaoh Deploy Autopilot for {$provisionerSettings["default"]}...", $this->getModuleName()) ;
                return $this->sshProvision($provisionerSettings, $init, $osProvisioner); }
            else if (isset($provisionerSettings["source"]) && $provisionerSettings["source"]=="guest") {
                $logging->log("Provisioning VM with local guest Pharaoh Deploy Autopilot for {$provisionerSettings["script"]}...", $this->getModuleName()) ;
                $init["provision_file"] = $provisionerSettings["script"] ;
                return $this->sshProvision($provisionerSettings, $init, $osProvisioner); }
            else {
                $logging->log("SFTP Application Deployment Autopilot for Pharaoh Deploy...", $this->getModuleName()) ;
                $init["provision_file"] = $provisionerSettings["script"] ;
                $this->sftpProvision($provisionerSettings, $init);
                $logging->log("SSH Execute Provisioning Guest with Pharaoh Deploy...", $this->getModuleName()) ;
                return $this->sshProvision($provisionerSettings, $init, $osProvisioner); } }
        else if ($provisionerSettings["target"] == "host") {
            $logging->log("Provisioning Host with PTDeploy Starting...", $this->getModuleName()) ;
            $sys = new \Model\SystemDetectionAllOS();
            $prefix = (!in_array($sys->os, array("Windows", "WINNT"))) ? "sudo " : "" ;
            $command = $prefix.PTDCOMM.' auto x --af="'.$provisionerSettings["script"].'"' ;
            if (isset($provisionerSettings["params"])) {
                foreach ($provisionerSettings["params"] as $paramkey => $paramval) {
                    if (is_array($paramval)) {
                        $command .= " --$paramkey=\"".implode(',', $paramval)."\"";
                    } else {
                        $command .= " --$paramkey=\"$paramval\"" ;
                    } } }
            echo $command."\n" ;
//            self::executeAndOutput() ;
            $rc = self::executeAndGetReturnCode($command, true, true) ;
            $logging->log("Provisioning Host with Pharaoh Deploy Complete...", $this->getModuleName()) ;
            if ($rc["rc"] !== 0) {
                $logging->log("Provisioning Host with Pharaoh Deploy Failed...", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
                return false ; }
            return true ; }
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
        return $sftp->performSFTPPut();
    }

    protected function sshProvision($provisionerSettings, $init, $osProvisioner) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $sshParams = $this->params ;
        $psparams = (isset($provisionerSettings["params"])) ? $provisionerSettings["params"] : array() ;

        if (isset($provisionerSettings["default"])) {
            $logging->log("Attempting to use default {$provisionerSettings["tool"]} script {$provisionerSettings["default"]}", $this->getModuleName()) ;
            $methodName = "get".ucfirst($provisionerSettings["default"])."SSHData" ;
            if (method_exists($osProvisioner, $methodName)) {
                $logging->log("Found $methodName method in OS Provisioner", $this->getModuleName()) ;
                $sshParams["ssh-data"] = $osProvisioner->$methodName($init["provision_file"], $psparams) ; }
            else {
                $logging->log("No method $methodName found in OS Provisioner, cannot continue", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
                return false ; } }
        else {
            $tool = ucfirst($provisionerSettings["tool"]) ;
            $logging->log("Attempting to use {$tool} script {$provisionerSettings["script"]}", $this->getModuleName()) ;
            $methodName = "getStandard{$tool}SSHData" ;
            if (method_exists($osProvisioner, $methodName)) {
                $logging->log("Found $methodName method in OS Provisioner", $this->getModuleName()) ;
                $sshParams["ssh-data"] = $osProvisioner->$methodName($init["provision_file"], $psparams ) ; }
            else {
                $logging->log("No method $methodName found in OS Provisioner, cannot continue", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
                return false ; } }

        $sshParams["yes"] = "true" ;
        $sshParams["guess"] = "true" ;
        $sshParams["servers"] = $init["encoded_box"] ;
        $sshParams["driver"] = $this->virtufile->config["ssh"]["driver"] ;
        if (isset($this->virtufile->config["ssh"]["port"])) {
            $sshParams["port"] = $this->virtufile->config["ssh"]["port"] ; }
        if (isset($this->virtufile->config["ssh"]["timeout"])) {
            $sshParams["timeout"] = $this->virtufile->config["ssh"]["timeout"] ; }
        $sshFactory = new \Model\Invoke();
        $ssh = $sshFactory->getModel($sshParams) ;
        return $ssh->performInvokeSSHData() ;
    }

    protected function keyboardProvision($provisionerSettings, $init, $osProvisioner) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $sshParams = $this->params ;
        $psparams = (isset($provisionerSettings["params"])) ? $provisionerSettings["params"] : array() ;

        if (isset($provisionerSettings["default"])) {
            $logging->log("Attempting to use default {$provisionerSettings["tool"]} script {$provisionerSettings["default"]}", $this->getModuleName()) ;
            $methodName = "get".ucfirst($provisionerSettings["default"])."SSHData" ;
            if (method_exists($osProvisioner, $methodName)) {
                $logging->log("Found $methodName method in OS Provisioner", $this->getModuleName()) ;
                $sshParams["ssh-data"] = $osProvisioner->$methodName($init["provision_file"], $psparams) ; }
            else {
                $logging->log("No method $methodName found in OS Provisioner, cannot continue", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
                return false ; } }
        else {
            $tool = ucfirst($provisionerSettings["tool"]) ;
            $logging->log("Attempting to use {$tool} script {$provisionerSettings["script"]}", $this->getModuleName()) ;
            $methodName = "getStandard{$tool}SSHData" ;
            if (method_exists($osProvisioner, $methodName)) {
                $logging->log("Found $methodName method in OS Provisioner", $this->getModuleName()) ;
                $sshParams["ssh-data"] = $osProvisioner->$methodName($init["provision_file"], $psparams ) ; }
            else {
                $logging->log("No method $methodName found in OS Provisioner, cannot continue", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
                return false ; } }

        $sshParams["yes"] = true ;
        $sshParams["guess"] = true ;
//        $sshParams["servers"] = $init["encoded_box"] ;
//        $sshParams["driver"] = $this->virtufile->config["ssh"]["driver"] ;
//        if (isset($this->virtufile->config["ssh"]["port"])) {
//            $sshParams["port"] = $this->virtufile->config["ssh"]["port"] ; }
//        if (isset($this->virtufile->config["ssh"]["timeout"])) {
//            $sshParams["timeout"] = $this->virtufile->config["ssh"]["timeout"] ; }
        $sshFactory = new \Model\VirtualKeyboard();
        $ssh = $sshFactory->getModel($sshParams) ;
        return $ssh->performInvokeSSHData() ;
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
                        $logging->log("Found $ip...", $this->getModuleName()) ;
                        if ($cards==count($ips)) { return $ips ; } } } }
            echo "." ;
            sleep(1) ; }
        echo "\n" ;

        return $ips ;
    }

    protected function waitForSsh($ips, $thisPort) {
        $t = 0;
        $totalTime =
            (isset($this->virtufile->config["vm"]["ssh_find_timeout"])) ?
                $this->virtufile->config["vm"]["ssh_find_timeout"] : 300 ;
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $logging->log("Waiting for ssh...", $this->getModuleName()) ;
        while ($t < $totalTime) {
            foreach ($ips as $ip) {
                $command = PTCCOMM." port is-responding --ip=$ip --port-number=$thisPort" ;
                $vmInfo = self::executeAndLoad($command) ;
                if (strpos($vmInfo, "Success") != false) {
                    $logging->log("IP $ip and Port $thisPort are responding, we'll use those...", $this->getModuleName()) ;
                    return $ip ; }
                echo "." ;
                $t = $t+1; }
            sleep(1) ; }
        echo "\n" ;
        return null ;
    }

    protected function loadPapyrusLocal() {
        $prFactory = new \Model\PTVirtualizeRequired() ;
        $papyrusLocalLoader = $prFactory->getModel($this->params, "PapyrusLocalLoader") ;
        return $papyrusLocalLoader->load($this->virtufile) ;
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