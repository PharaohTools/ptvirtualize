<?php

Namespace Model;

class UpModifyVMAllLinux extends BaseLinuxApp {

    // Compatibility
    public $os = array("Linux") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("ModifyVM") ;

    public $papyrus;
    public $phlagrantfile;
    protected $availableModifications;
    protected $availableNetworkModifications;

    public function performModifications() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $this->setAvailableModifications();
        foreach ($this->phlagrantfile->config["vm"] as $configKey => $configValue) {
            if (in_array($configKey, $this->availableModifications)) {
                $logging->log("Modifying VM {$this->phlagrantfile->config["vm"]["name"]} system by changing $configKey to $configValue") ;
                $command = "vboxmanage modifyvm {$this->phlagrantfile->config["vm"]["name"]} --$configKey $configValue" ;
                $this->executeAndOutput($command); } }
        $this->setAvailableNetworkModifications();
        foreach ($this->phlagrantfile->config["network"] as $configKey => $configValue) {
            if (in_array($configKey, $this->availableNetworkModifications)) {
                $logging->log("Modifying VM {$this->phlagrantfile->config["vm"]["name"]} network by changing $configKey to $configValue") ;
                $command = "vboxmanage modifyvm {$this->phlagrantfile->config["vm"]["name"]} --$configKey $configValue" ;
                $this->executeAndOutput($command); } }
        $this->setSharedFolders();
    }

    /// @todo can we pull this information from vboxmanage, then we dont have to udate this method when vboxmanage changes
    protected function setAvailableModifications() {
        $this->availableModifications = array(
            "name", "ostype", "memory", "vram", "cpus", "cpuexecutioncap", "boot", "graphicscontroller", "monitorcount",
            "draganddrop", "usb", "usbehci", "snapshotfolder", "autostart-enabled", "autostart-delay", "groups",
            "iconfile", "pagefusion", "acpi", "ioapic", "hpet", "triplefaultreset", "hwvirtex", "nestedpaging",
            "largepages", "vtxvpid", "vtxux", "pae", "longmode", "synthcpu", "cpuidset", "cpuidremove",
            "cpuidremoveall", "hardwareuuid", "cpuhotplug", "plugcpu", "unplugcpu", "rtcuseutc", "accelerate3d",
            "firmware", "chipset", "bioslogofadein", "bioslogofadeout", "bioslogodisplaytime", "bioslogoimagepath",
            "biosbootmenu", "biossystemtimeoffset", "biospxedebug", "mouse", "keyboard", "guestmemoryballoon", "audio",
            "audiocontroller", "clipboard", "vrde", "vrdeextpack", "vrdeproperty", "vrdeport", "vrdeaddress",
            "vrdeauthtype", "vrdeauthlibrary", "vrdemulticon", "vrdereusecon", "vrdevideochannel",
            "vrdevideochannelquality", "teleporter", "teleporterport", "teleporteraddress", "teleporterpassword",
            "teleporterpasswordfile", "tracing-enabled", "tracing-config", "tracing-allow-vm-access", "defaultfrontend"
        );
    }

    protected function setAvailableNetworkModifications() {

        for ($i = 0; $i<10; $i++) {
            $this->availableNetworkModifications[] = "nic$i" ;
            $this->availableNetworkModifications[] = "nictype$i" ;
            $this->availableNetworkModifications[] = "cableconnected$i" ;
            $this->availableNetworkModifications[] = "nictrace$i" ;
            $this->availableNetworkModifications[] = "nictracefile$i" ;
            $this->availableNetworkModifications[] = "bridgeadapter$i" ;
            $this->availableNetworkModifications[] = "hostonlyadapter$i" ;
            $this->availableNetworkModifications[] = "intnet$i" ;
            $this->availableNetworkModifications[] = "macaddress$i" ;
            $this->availableNetworkModifications[] = "natpf$i" ;
        }

    }

    protected function setSharedFolders() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (isset($this->phlagrantfile->config["vm"]["shared_folders"]) && count($this->phlagrantfile->config["vm"]["shared_folders"])>0 ) {
        foreach ($this->phlagrantfile->config["vm"]["shared_folders"] as $sharedFolder) {

            // @todo we check if a shared folder by this name exists. if it does, we delete it

            $logging->log("Adding Shared Folder named {$sharedFolder["name"]} to VM {$this->phlagrantfile->config["vm"]["name"]} to Host path {$sharedFolder["host_path"]}") ;
            $command  = "vboxmanage sharedfolder add {$this->phlagrantfile->config["vm"]["name"]} --name {$sharedFolder["name"]} " ;
            $command .= " --hostpath {$sharedFolder["host_path"]}" ;
            $flags = array("transient", "readonly", "automount") ;
            foreach ($flags as $flag) {
                if (isset($sharedFolder[$flag])) {
                    $command .= " --$flag" ; } }
            $this->executeAndOutput($command); } }
    }

}