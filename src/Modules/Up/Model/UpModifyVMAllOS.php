<?php

Namespace Model;

class UpModifyVMAllOS extends BaseFunctionModel {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("ModifyVM") ;

    protected $availableModifications;
    protected $availableNetworkModifications;

    public function performModifications() {
        $this->loadFiles();
        $this->findProvider("UpModify");
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $this->setAvailableModifications();
        if (isset($this->phlagrantfile->config["vm"]["hd_resize"])) {
            $this->modifyHardDisks(); }
        foreach ($this->phlagrantfile->config["vm"] as $configKey => $configValue) {
            if (in_array($configKey, $this->availableModifications)) {
                $logging->log("Modifying VM {$this->phlagrantfile->config["vm"]["name"]} system by changing $configKey to $configValue") ;
                $this->provider->modify($this->phlagrantfile->config["vm"]["name"], $configKey, $configValue); } }
        $this->setAvailableNetworkModifications();
        foreach ($this->phlagrantfile->config["network"] as $configKey => $configValue) {
            if (in_array($configKey, $this->availableNetworkModifications)) {
                $logging->log("Modifying VM {$this->phlagrantfile->config["vm"]["name"]} network by changing $configKey to $configValue") ;
                $this->provider->modify($this->phlagrantfile->config["vm"]["name"], $configKey, $configValue); } }
        $this->setSharedFolders();
    }

    public function removeShares() {
        $this->loadFiles();
        $this->findProvider("UpModify");
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Removing shared folders") ;
        $this->destroyExistingShares();
    }

    /// @todo can we pull this information from vboxmanage, then we dont have to udate this method when vboxmanage changes
    protected function setAvailableModifications() {
        $this->availableModifications = $this->provider->getAvailableModifications();
    }

    protected function setAvailableNetworkModifications() {
        $this->availableModifications = $this->provider->getAvailableModifications();

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
        $this->loadFiles();
        $this->findProvider("UpModify");
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (isset($this->phlagrantfile->config["vm"]["shared_folders"]) && count($this->phlagrantfile->config["vm"]["shared_folders"])>0 ) {
            $this->destroyExistingShares();
            foreach ($this->phlagrantfile->config["vm"]["shared_folders"] as $sharedFolder) {
                $logging->log("Adding Shared Folder named {$sharedFolder["name"]} to VM {$this->phlagrantfile->config["vm"]["name"]} to Host path {$sharedFolder["host_path"]}") ;
                $this->provider->addShare($this->phlagrantfile->config["vm"]["name"], $sharedFolder); } }
    }


    protected function destroyExistingShares() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Finding existing shares") ;
        $names = $this->provider->getShares($this->phlagrantfile->config["vm"]["name"]);
        foreach ($names as $share) {
            $logging->log("Removing Shared Folder named {$share} from VM {$this->phlagrantfile->config["vm"]["name"]}") ;
            $this->provider->removeShare($this->phlagrantfile->config["vm"]["name"], $share); }
    }

    protected function modifyHardDisks() {
        $this->loadFiles();
        $this->findProvider("UpModify");
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Phlagrantfile specifies Resizing HD for VM {$this->phlagrantfile->config["vm"]["name"]}") ;
        $logging->log("Finding existing hard disks") ;
        $disks = $this->provider->getDisks($this->phlagrantfile->config["vm"]["name"]);
        foreach ($disks as $disk) {
            $logging->log("Modifying HD $disk system by changing size to {$this->phlagrantfile->config["vm"]["hd_resize"]}") ;
            $this->provider->modifyDisk($disk, $this->phlagrantfile->config["vm"]["hd_resize"]); }
    }

}