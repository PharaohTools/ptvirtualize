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

    public function performModifications() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $this->setAvailableModifications();
        foreach ($this->phlagrantfile->config["vm"] as $configKey => $configValue) {
            if (in_array($configKey, $this->availableModifications)) {
                $logging->log("Modifying VM {$this->phlagrantfile->config["vm"]["name"]} by changing $configKey to $configValue") ;
                $command = "vboxmanage modifyvm {$this->phlagrantfile->config["vm"]["name"]} --$configKey $configValue" ;
                $this->executeAndOutput($command); } }
        $this->setSharedFolders();
    }

    protected function setAvailableModifications() {
        $this->availableModifications = array(
            "memory", "cpus"
        ) ;
    }

    protected function setSharedFolders() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (isset($this->phlagrantfile->config["shared_folders"]) && count($this->phlagrantfile->config["shared_folders"])>0 ) {
        foreach ($this->phlagrantfile->config["shared_folders"] as $sharedFolder) {
            if (in_array($sharedFolder, $this->availableModifications)) {
                $logging->log("Adding Shared Folder named {$sharedFolder["name"]} to VM {$this->phlagrantfile->config["vm"]["name"]} to Host path {$sharedFolder["path_host"]}") ;
                $command  = "vboxmanage sharedfolder add {$this->phlagrantfile->config["vm"]["name"]} --name {$sharedFolder["name"]} " ;
                $command .= " --hostpath {$sharedFolder["path_host"]}" ;
                $flags = array("transient", "readonly", "automount") ;
                foreach ($flags as $flag) {
                    if (isset($sharedFolder[$flag])) {
                        $command .= " --$flag" ; } }
                $this->executeAndOutput($command); } } }
    }

    /*
     * @todo
     *   sharedfolder
                            add <uuid|vmname>
                            --name <name> --hostpath <hostpath>
                            [--transient] [--readonly] [--automount]

  sharedfolder              remove <uuid|vmname>
                            --name <name> [--transient]


                                [--name <name>]
                            [--groups <group>, ...]
                            [--ostype <ostype>]
                            [--iconfile <filename>]
                            [--pagefusion on|off]
                            [--vram <vramsize in MB>]
                            [--acpi on|off]
                            [--ioapic on|off]
                            [--hpet on|off]
                            [--triplefaultreset on|off]
                            [--hwvirtex on|off]
                            [--nestedpaging on|off]
                            [--largepages on|off]
                            [--vtxvpid on|off]
                            [--vtxux on|off]
                            [--pae on|off]
                            [--longmode on|off]
                            [--synthcpu on|off]
                            [--cpuidset <leaf> <eax> <ebx> <ecx> <edx>]
                            [--cpuidremove <leaf>]
                            [--cpuidremoveall]
                            [--hardwareuuid <uuid>]
                            [--cpuhotplug on|off]
                            [--plugcpu <id>]
                            [--unplugcpu <id>]
                            [--cpuexecutioncap <1-100>]
                            [--rtcuseutc on|off]
                            [--accelerate3d on|off]
                            [--firmware bios|efi|efi32|efi64]
                            [--chipset ich9|piix3]
                            [--bioslogofadein on|off]
                            [--bioslogofadeout on|off]
                            [--bioslogodisplaytime <msec>]
                            [--bioslogoimagepath <imagepath>]
                            [--biosbootmenu disabled|menuonly|messageandmenu]
                            [--biossystemtimeoffset <msec>]
                            [--biospxedebug on|off]
                            [--nictype<1-N> Am79C970A|Am79C973]
                            [--cableconnected<1-N> on|off]
                            [--nictrace<1-N> on|off]
                            [--nictracefile<1-N> <filename>]
                            [--nicproperty<1-N> name=[value]]
                            [--nicspeed<1-N> <kbps>]
                            [--nicbootprio<1-N> <priority>]
                            [--nicpromisc<1-N> deny|allow-vms|allow-all]
                            [--nicbandwidthgroup<1-N> none|<name>]
                            [--bridgeadapter<1-N> none|<devicename>]
                            [--intnet<1-N> <network name>]
                            [--nat-network<1-N> <network name>]
                            [--nicgenericdrv<1-N> <driver>
                            [--natnet<1-N> <network>|default]
                            [--natsettings<1-N> [<mtu>],[<socksnd>],
                                                [<sockrcv>],[<tcpsnd>],
                                                [<tcprcv>]]
                            [--natpf<1-N> [<rulename>],tcp|udp,[<hostip>],
                                          <hostport>,[<guestip>],<guestport>]
                            [--natpf<1-N> delete <rulename>]
                            [--nattftpprefix<1-N> <prefix>]
                            [--nattftpfile<1-N> <file>]
                            [--nattftpserver<1-N> <ip>]
                            [--natbindip<1-N> <ip>
                            [--natdnspassdomain<1-N> on|off]
                            [--natdnsproxy<1-N> on|off]
                            [--natdnshostresolver<1-N> on|off]
                            [--nataliasmode<1-N> default|[log],[proxyonly],
                                                         [sameports]]
                            [--macaddress<1-N> auto|<mac>]
                            [--mouse ps2|usb|usbtablet|usbmultitouch]
                            [--keyboard ps2|usb
                            [--uart<1-N> off|<I/O base> <IRQ>]
                            [--uartmode<1-N> disconnected|
                                             server <pipe>|
                                             client <pipe>|
                                             file <file>|
                                             <devicename>]
                            [--lpt<1-N> off|<I/O base> <IRQ>]
                            [--lptmode<1-N> <devicename>]
                            [--guestmemoryballoon <balloonsize in MB>]
                            [--audio none|null|dsound|solaudio|oss|
                                     oss|coreaudio]
                            [--audiocontroller ac97|hda|sb16]
                            [--clipboard disabled|hosttoguest|guesttohost|
                                         bidirectional]
                            [--vrde on|off]
                            [--vrdeextpack default|<name>
                            [--vrdeproperty <name=[value]>]
                            [--vrdeport <hostport>]
                            [--vrdeaddress <hostip>]
                            [--vrdeauthtype null|external|guest]
                            [--vrdeauthlibrary default|<name>
                            [--vrdemulticon on|off]
                            [--vrdereusecon on|off]
                            [--vrdevideochannel on|off]
                            [--vrdevideochannelquality <percent>]
                            [--teleporter on|off]
                            [--teleporterport <port>]
                            [--teleporteraddress <address|empty>
                            [--teleporterpassword <password>]
                            [--teleporterpasswordfile <file>|stdin]
                            [--tracing-enabled on|off]
                            [--tracing-config <config-string>]
                            [--tracing-allow-vm-access on|off]
                            [--defaultfrontend default|<name>]

                            // ones to do first

                            [--cpuexecutioncap <1-100>]
                            [--boot<1-4> none|floppy|dvd|disk|net>]
                            [--nic<1-N> none|null|nat|bridged|intnet|
                                        generic|natnetwork]
                            [--graphicscontroller none|vboxvga]
                            [--monitorcount <number>]
                            [--draganddrop disabled|hosttoguest
                            [--usb on|off]
                            [--usbehci on|off]
                            [--snapshotfolder default|<path>]
                            [--autostart-enabled on|off]
                            [--autostart-delay <seconds>]
                            [--mouse ps2|usb|usbtablet|usbmultitouch]
                            [--keyboard ps2|usb

                            // done

                            [--cpus <number>]
                            [--memory <memorysize in MB>]


     */

}