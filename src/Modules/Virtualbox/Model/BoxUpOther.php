<?php

Namespace Model;

class BoxUpModify extends BaseFunctionModel {

    // Compatibility
    public $os = array("Linux", "Darwin") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("UpModify") ;

    //@todo need windows version
    public function modify($name, $key, $value) {
        $command = VBOXMGCOMM." modifyvm {$name} --$key \"$value\"" ;
        $this->executeAndOutput($command);
        $command = "echo $?" ;
        $ret = $this->executeAndLoad($command);
        return ($ret == "0") ? true : false ;
    }

    //@todo need windows version
    public function getShares($name) {
        $command  = VBOXMGCOMM." showvminfo {$name}" ;
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
        return $names ;
    }

    //@todo need windows version
    public function removeShare($name, $share) {
        $command  = VBOXMGCOMM." sharedfolder remove {$name} --name {$share} " ;
        $this->executeAndOutput($command);
        $command = "echo $?" ;
        $ret = $this->executeAndLoad($command);
        return ($ret == "0") ? true : false ;
    }

    //@todo need windows version
    public function addShare($name, $sharedFolder) {
        $command  = VBOXMGCOMM." sharedfolder add {$name} --name {$sharedFolder["name"]} " ;
        $command .= " --hostpath {$sharedFolder["host_path"]}" ;
        $flags = array("transient", "readonly", "automount") ;
        foreach ($flags as $flag) {
            if (isset($sharedFolder[$flag])) {
                $command .= " --$flag" ; } }
        $this->executeAndOutput($command);
        $command = "echo $?" ;
        $ret = $this->executeAndLoad($command);
        return ($ret == "0") ? true : false ;
    }

    //@todo need windows version
    public function getDisks($name) {
        $command  = VBOXMGCOMM." showvminfo {$this->phlagrantfile->config["vm"]["name"]}" ;
        $out = $this->executeAndLoad($command);
        $lines = explode("\n", $out) ;

        foreach ($lines as $oneline) {
            if (strpos($oneline, "SATA (0, 0): ")===0) {
                $start = strpos($oneline, '(UUID: ')+6 ;
                $end = strpos($oneline, ')') ;
                $uuid = substr($oneline, $start, $end) ;

                $uuids[] = $uuid ; } }

    }

    //@todo need windows version
    public function modifyDisk($disk, $size) {
        $command = VBOXMGCOMM." modifyhd $disk --resize {$size}" ;
        $this->executeAndOutput($command);
        $command = "echo $?" ;
        $ret = $this->executeAndLoad($command);
        return ($ret == "0") ? true : false ;
    }

    /// @todo can we pull this information from vboxmanage, then we dont have to udate this method when vboxmanage changes
    public function getAvailableModifications() {
        return array(
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


}