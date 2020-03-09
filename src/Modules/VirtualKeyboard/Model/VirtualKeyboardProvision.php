<?php

Namespace Model;

class VirtualKeyboardProvision extends BaseVirtualKeyboardAllOS {

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
        $virtualKeyboardSpellings = array("virtualKeyboard", "virtual-keyboard", "key", "keyboard");
        if (in_array($provisionerSettings["tool"], $virtualKeyboardSpellings)) {
            $logging->log("Initialising Virtual Keyboard Provision... ", $this->getModuleName());
            $init = $this->initialiseVirtualKeyboardProvision($provisionerSettings) ;
            return $this->virtualKeyboardProvision($provisionerSettings, $init, $osProvisioner) ; }
        else {
            $logging->log("Unrecognised Virtual Keyboard Provisioning Tool {$provisionerSettings["tool"]} ".var_export($provisionerSettings, true)." specified", $this->getModuleName(), LOG_FAILURE_EXIT_CODE);
            return false ; }
    }

    protected function virtualKeyboardProvision($provisionerSettings, $init, $osProvisioner) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $logging->log("Provisioning Guest with VirtualKeyboard...", $this->getModuleName());

        if (isset($provisionerSettings["default"])) {
            $logging->log("Attempting to use default shell script {$provisionerSettings["default"]}", $this->getModuleName());
            $methodName = "get".ucfirst($provisionerSettings["default"])."SSHData" ;
            if (method_exists($osProvisioner, $methodName)) {
                $logging->log("Found {$provisionerSettings["default"]} method in OS Provisioner", $this->getModuleName());
                $keyboard_data = $osProvisioner->$methodName($init["provision_file"], $provisionerSettings) ; }
            else {
                $logging->log("No method {$provisionerSettings["default"]} found in OS Provisioner, cannot continue", $this->getModuleName(), LOG_FAILURE_EXIT_CODE);
                return false ; } }
        else {
            $logging->log("Attempting to use Standard shell script {$provisionerSettings["default"]}", $this->getModuleName());
            $methodName = "getStandardShellSSHData" ;
            if (method_exists($osProvisioner, $methodName)) {
                $logging->log("Found {$methodName} method in OS Provisioner", $this->getModuleName());
                $keyboard_data = $osProvisioner->$methodName($provisionerSettings["default"]) ; }
            else {
                $logging->log("No method {$methodName} found in OS Provisioner, cannot continue", $this->getModuleName(), LOG_FAILURE_EXIT_CODE);
                return false ; }
        }

        $tempfile = tempnam('/tmp/', 'ptv_provision_'.time()) ;
        $tempfile .= '.bash' ;
        touch($tempfile) ;
        $script_start = "#!/usr/bin/env bash\n\nset -ex\n\n" ;
        file_put_contents($tempfile, $script_start.$keyboard_data) ;

        $c1 = VBOXMGCOMM.' guestcontrol '.$this->virtufile->config["vm"]["name"].
            ' --username '.$this->virtufile->config["ssh"]["user"].
            ' --password '.$this->virtufile->config["ssh"]["password"].
        ' copyto '.$tempfile.' '.$tempfile ;

        $rc = self::executeAndGetReturnCode($c1) ;
        if ($rc['rc'] !== 0) {
            $logging->log("Copy command failed ".var_export($rc, true), $this->getModuleName(), LOG_FAILURE_EXIT_CODE);
            unlink($tempfile) ;
            return false ;
        }

        $c2 = VBOXMGCOMM.' guestcontrol '.$this->virtufile->config["vm"]["name"].
            ' --username '.$this->virtufile->config["ssh"]["user"].
            ' --password '.$this->virtufile->config["ssh"]["password"].
            ' run --exe "/bin/chmod" -- "-R" "777" "'.$tempfile.'"' ;

        $rc = self::executeAndGetReturnCode($c2) ;
        if ($rc['rc'] !== 0) {
            $logging->log("Permissions command failed", $this->getModuleName(), LOG_FAILURE_EXIT_CODE);
            unlink($tempfile) ;
            return false ;
        }

        $c3 = VBOXMGCOMM.' guestcontrol '.$this->virtufile->config["vm"]["name"].
            ' --username '.$this->virtufile->config["ssh"]["user"].
            ' --password '.$this->virtufile->config["ssh"]["password"].
            ' run --exe "'.$tempfile.'"' ;

        $rc = self::executeAndGetReturnCode($c3) ;
        if ($rc['rc'] !== 0) {
            $logging->log("Provision execution failed", $this->getModuleName(), LOG_FAILURE_EXIT_CODE);
            unlink($tempfile) ;
            return false ;
        }

        $c4 = VBOXMGCOMM.' guestcontrol '.$this->virtufile->config["vm"]["name"].
            ' --username '.$this->virtufile->config["ssh"]["user"].
            ' --password '.$this->virtufile->config["ssh"]["password"].
            ' removefile "'.$tempfile.'"' ;

        $rc = self::executeAndGetReturnCode($c4) ;
        if ($rc['rc'] !== 0) {
            $logging->log("Provision Script command failed", $this->getModuleName(), LOG_FAILURE_EXIT_CODE);
            unlink($tempfile) ;
            return false ;
        }

        $logging->log("Provision Complete", $this->getModuleName(), LOG_FAILURE_EXIT_CODE);
        unlink($tempfile) ;
        return true ;

    }

    protected function initialiseVirtualKeyboardProvision($provisionerSettings) {
        if ($provisionerSettings["target"] == "guest") {
            $loggingFactory = new \Model\Logging();
            $logging = $loggingFactory->getModel($this->params);
            $logging->log("Using Keyboard Shell Provision", $this->getModuleName());
            $encodedBox = serialize(array(array(
                "user" => "{$this->virtufile->config["ssh"]["user"]}",
                "password" => "{$this->virtufile->config["ssh"]["password"]}"
            ))) ;
//            $this->storeInPapyrus($this->virtufile->config["ssh"]["user"], $this->virtufile->config["ssh"]["password"], 'keyboard') ;
            $provisionFile = $this->virtufile->config["vm"]["default_tmp_dir"]."provision.sh" ;
            $ray = array() ;
            $ray["provision_file"] = $provisionFile ;
            $ray["encoded_box"] = $encodedBox ;
            $ray["provision"] = $provisionFile ; }
        else {
            $ray = array() ; }
        return $ray ;
    }

}