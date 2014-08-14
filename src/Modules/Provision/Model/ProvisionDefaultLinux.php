<?php

Namespace Model ;

class ProvisionDefaultLinux extends Base {

    public $phlagrantfile;
    public $papyrus ;

    // @todo provisioners should have their own modules, and the pharoahtools code should go there
    public function provision() {
        $pharoahSpellings = array("Pharaoh", "pharaoh", "PharaohTools", "pharaohTools", "Pharoah", "pharoah",
            "PharoahTools", "pharoahTools") ;
        foreach ($this->phlagrantfile->config["vm"]["provision"] as $provisioner) {
            if (in_array($provisioner[""], $pharoahSpellings)) {

                $this->pharoahProvision($provisioner) ;
            }
        }

    }

    protected function pharoahProvision($provisioner) {

        // @todo this should support other provisioners than pharoah
        $provisionFile = $this->phlagrantfile->config["vm"]["default_tmp_dir"]."/provision.php" ;
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        // get target ip from phlagrantfile if its there
        // if not check for guest additions installed
        $ips = array() ;
        if (isset($this->phlagrantfile->config["ssh"]["target"])) {
            $logging->log("Using Phlagrantfile defined ssh target of {$this->phlagrantfile->config["ssh"]["target"]}: ") ;
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

        foreach ($ips as $ip) {
            $res = $this->waitForSsh($ip, $thisPort, 2) ;
            if ($res == true) {
                $chosenIp = $ip ; } }

        $encodedBox = serialize(array(array(
            "user" => "{$this->phlagrantfile->config["ssh"]["user"]}",
            "password" => "{$this->phlagrantfile->config["ssh"]["password"]}",
            "target" => "$chosenIp"
        ))) ;


    }

    protected function cleopatraProvision() {

    }

    protected function dapperstranoProvision() {

    }

    protected function sftpProvision() {


        $sftpParams = $this->params ;
        $sftpParams["yes"] = true ;
        $sftpParams["guess"] = true ;
        $sftpParams["servers"] = $encodedBox ;
        $sftpParams["source"] = getcwd()."/build/config/cleopatra/cleofy/autopilots/generic/Phlagrant/cleofy-cm-phlagrant.php" ;
        $sftpParams["target"] = $provisionFile ;
        if (isset($this->phlagrantfile->config["ssh"]["port"])) {
            $sftpParams["port"] = $this->phlagrantfile->config["ssh"]["port"] ; }
        if (isset($this->phlagrantfile->config["ssh"]["timeout"])) {
            $sftpParams["timeout"] = $this->phlagrantfile->config["ssh"]["timeout"] ; }
        // var_dump('$sftpParams', $sftpParams) ;
        $sftpFactory = new \Model\SFTP();
        $sftp = $sftpFactory->getModel($sftpParams) ;
        $sftp->performSFTPPut();
    }

    protected function sshProvision() {

        $sshParams = $this->params ;
        $sshParams["ssh-data"] = $this->setSSHData($provisionFile);
        $sshParams["yes"] = true ;
        $sshParams["guess"] = true ;
        $sshParams["servers"] = $encodedBox ;
        if (isset($this->phlagrantfile->config["ssh"]["port"])) {
            $sshParams["port"] = $this->phlagrantfile->config["ssh"]["port"] ; }
        if (isset($this->phlagrantfile->config["ssh"]["timeout"])) {
            $sshParams["timeout"] = $this->phlagrantfile->config["ssh"]["timeout"] ; }
        // var_dump('$sshParams', $sshParams) ;
        $sshFactory = new \Model\Invoke();
        $ssh = $sshFactory->getModel($sshParams) ;
        $ssh->performInvokeSSHData() ;
    }

    protected function waitUntilGetIP() {
        $totalTime = (isset($this->phlagrantfile->config["vm"]["ip_find_timeout"]))
            ? $this->phlagrantfile->config["ssh"]["ip_find_timeout"] : 180 ;
        $ips = array() ;
        //while ($t < $totalTime) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $command = "vboxmanage guestproperty enumerate {$this->phlagrantfile->config["vm"]["name"]} | grep \"V4/IP\" " ;
        for ($secs = 0; $secs<$totalTime; $secs++) {
            $vmInfo = self::executeAndLoad($command) ;
            // var_dump("secs", $secs, "vmi", $vmInfo) ;
            for ($i=0;$i<30;$i++) { //for up to 30 ifaces
                $pattern = "/VirtualBox/GuestInfo/Net/$i/V4/IP" ;
                $sp = strpos($vmInfo, $pattern) ;
                if ($sp != false) {
                    $afterValue = substr($vmInfo, $sp+strlen($pattern)+9, 27) ;
                    // var_dump("av", $afterValue);
                    $endOfIp = strpos($afterValue, ",") ;
                    // var_dump("eoip", $endOfIp);
                    $ip = substr($afterValue, 0, $endOfIp) ;
                    // var_dump("ip", $ip);
                    if (!in_array($ip, $ips)) {
                        $ips[] = $ip ;
                        $logging->log("Found $ip...") ;} }}
            echo "." ;
            sleep(1) ; }
        return $ips ;
    }

    protected function waitForSsh($ip, $thisPort) {
        $t = 0;
        $totalTime = (isset($this->phlagrantfile->config["vm"]["ssh_find_timeout"]))
            ? $this->phlagrantfile->config["ssh"]["ssh_find_timeout"] : 300 ;
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

    protected function getDefaultIp() {
        return array("10.0.2.15", "192.168.56.1" ) ;
    }

    // @todo ahem
    protected function checkForGuestAdditions() {
        return true ;
    }

}
