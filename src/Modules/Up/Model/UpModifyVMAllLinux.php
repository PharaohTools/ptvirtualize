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
        if (isset($this->phlagrantfile->config["vm"]["hd_resize"])) {
            $this->modifyHardDisks(); }
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

    public function removeShares() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Removing shared folders") ;
        $this->destroyExistingShares();
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
            $this->destroyExistingShares();
            foreach ($this->phlagrantfile->config["vm"]["shared_folders"] as $sharedFolder) {
                $logging->log("Adding Shared Folder named {$sharedFolder["name"]} to VM {$this->phlagrantfile->config["vm"]["name"]} to Host path {$sharedFolder["host_path"]}") ;
                $command  = "vboxmanage sharedfolder add {$this->phlagrantfile->config["vm"]["name"]} --name {$sharedFolder["name"]} " ;
                $command .= " --hostpath {$sharedFolder["host_path"]}" ;
                $flags = array("transient", "readonly", "automount") ;
                foreach ($flags as $flag) {
                    if (isset($sharedFolder[$flag])) {
                        $command .= " --$flag" ; } }
                $this->executeAndOutput($command); } }
    }


    protected function destroyExistingShares() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Finding existing shares") ;
        $command  = "vboxmanage showvminfo {$this->phlagrantfile->config["vm"]["name"]}" ;
        $out = $this->executeAndLoad($command);
        $lines = explode("\n", $out) ;
        $names = array() ;
        foreach ($lines as $oneline) {
            if (strpos($oneline, "Shared folders")===0) {
                $cstill = 1;
                continue ; } // skip a line
            else {
                if (isset($cstill) && $cstill == 1) {
                    $cstill = 2;
                    continue ;  } // skip a line
                else if (isset($cstill) && $cstill == 2) {
                    if (strpos($oneline, "Name: '")===0) {
                        $end = str_replace("Name: '", "", $oneline) ;
                        $names[] = substr($end, 0, strpos($end, "'")) ; }
                    else {
                        break ;  } } } }

        foreach ($names as $name) {
            $logging->log("Removing Shared Folder named {$name} from VM {$this->phlagrantfile->config["vm"]["name"]}") ;
            $command  = "vboxmanage sharedfolder remove {$this->phlagrantfile->config["vm"]["name"]} --name {$name} " ;
            $this->executeAndOutput($command); }

    }

    protected function modifyHardDisks() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Phlagrantfile specifies Resizing HD for VM {$this->phlagrantfile->config["vm"]["name"]}") ;
        $logging->log("Finding existing hard disks") ;
        $command  = "vboxmanage showvminfo {$this->phlagrantfile->config["vm"]["name"]}" ;
        $out = $this->executeAndLoad($command);
        $lines = explode("\n", $out) ;
        foreach ($lines as $oneline) {
            if (strpos($oneline, "SATA (0, 0): ")===0) {
                $start = strpos($oneline, '(UUID: ')+6 ;
                $end = strpos($oneline, ')') ;
                $uuid = substr($oneline, $start, $end) ;
                $logging->log("Modifying HD $uuid system by changing size to {$this->phlagrantfile->config["vm"]["hd_resize"]}") ;
                $command = "vboxmanage modifyhd $uuid --resize {$this->phlagrantfile->config["vm"]["hd_resize"]}" ;
                $this->executeAndOutput($command); } }
    }

}