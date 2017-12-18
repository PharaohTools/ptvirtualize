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
//            $init = $this->initialiseVirtualKeyboardProvision($provisionerSettings) ;
            return $this->virtualKeyboardProvision($provisionerSettings, $osProvisioner) ; }
        else {
            $logging->log("Unrecognised Virtual Keyboard Provisioning Tool {$provisionerSettings["tool"]} specified", $this->getModuleName(), LOG_FAILURE_EXIT_CODE);
            return false ; }
    }

    protected function virtualKeyboardProvision($provisionerSettings, $osProvisioner) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $logging->log("Provisioning Host with VirtualKeyboard...", $this->getModuleName());
//        $command = "sh {$provisionerSettings["script"]}" ;

        if (isset($provisionerSettings["default"])) {
            $logging->log("Attempting to use default shell script {$provisionerSettings["default"]}", $this->getModuleName());
            $methodName = "get".ucfirst($provisionerSettings["default"])."SSHData" ;
            if (method_exists($osProvisioner, $methodName)) {
                $logging->log("Found {$provisionerSettings["default"]} method in OS Provisioner", $this->getModuleName());
                $keyboard_data = $osProvisioner->$methodName() ; }
            else {
                $logging->log("No method {$provisionerSettings["default"]} found in OS Provisioner, cannot continue", $this->getModuleName(), LOG_FAILURE_EXIT_CODE);
                return false ; } }
        else {
//            $logging->log("Attempting to use Standard shell script {$init["provision_file"]}", $this->getModuleName());
//            $methodName = "getStandardShellSSHData" ;
//            if (method_exists($osProvisioner, $methodName)) {
//                $logging->log("Found {$methodName} method in OS Provisioner", $this->getModuleName());
//                $sshParams["ssh-data"] = $osProvisioner->$methodName($init["provision_file"]) ; }
//            else {
//                $logging->log("No method {$methodName} found in OS Provisioner, cannot continue", $this->getModuleName(), LOG_FAILURE_EXIT_CODE);
//                return false ; }
        }

        var_dump("Keyboard Data", $keyboard_data) ;

        $command = "VBoxManage guestcontrol {$this->virtufile->config["vm"]["name"]} " ;
        $command .= "--username {$this->virtufile->config["ssh"]["user"]} " ;
        $command .= "--password {$this->virtufile->config["ssh"]["password"]} " ;
        $command .= "run --exe {$keyboard_data} " ;
        $command .= "--wait-stdout --wait-stderr " ;

        $rc = self::executeAndOutput($command, null, true) ;
        return ($rc === 0) ? true : false ;
    }

    // @todo ahem
    protected function checkForGuestAdditions() {
        return true ;
    }

}