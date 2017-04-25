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

    protected function virtualKeyboardProvision($provisioner, $osProvisioner) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $logging->log("Provisioning Host with VirtualKeyboard...", $this->getModuleName());
//        $command = "sh {$provisioner["script"]}" ;
        $command = "VBoxManage guestcontrol openstackdevstack " ;
        $command .= "--username {$this->virtufile->config["ssh"]["user"]} " ;
        $command .= "--password {$this->virtufile->config["ssh"]["password"]} " ;
        $command .= "run --exe {$provisioner["script"]} " ;
        $command .= "--wait-stdout --wait-stderr " ;
        # VBoxManage guestcontrol openstackdevstack --username stack --password stack run --exe /opt/devstack/stack_wrap.sh --wait-stdout --wait-stderr
        $rc = self::executeAndOutput($command, null, true) ;
        return ($rc === 0) ? true : false ;
    }

    // @todo ahem
    protected function checkForGuestAdditions() {
        return true ;
    }

}