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
        if (isset($this->virtualizerfile->config["vm"]["hd_resize"])) {
            $this->modifyHardDisks(); }
        foreach ($this->virtualizerfile->config["vm"] as $configKey => $configValue) {
            if (in_array($configKey, $this->availableModifications)) {
                $logging->log("Modifying VM {$this->virtualizerfile->config["vm"]["name"]} system by changing $configKey to $configValue") ;
                $this->provider->modify($this->virtualizerfile->config["vm"]["name"], $configKey, $configValue); } }
        $this->setAvailableNetworkModifications();
        foreach ($this->virtualizerfile->config["network"] as $configKey => $configValue) {
            if (in_array($configKey, $this->availableNetworkModifications)) {
                $logging->log("Modifying VM {$this->virtualizerfile->config["vm"]["name"]} network by changing $configKey to $configValue") ;
                $this->provider->modify($this->virtualizerfile->config["vm"]["name"], $configKey, $configValue); } }
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
        $this->availableNetworkModifications = $this->provider->availableNetworkModifications();
    }

    protected function setSharedFolders() {
        $this->loadFiles();
        $this->findProvider("UpModify");
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (isset($this->virtualizerfile->config["vm"]["shared_folders"]) && count($this->virtualizerfile->config["vm"]["shared_folders"])>0 ) {
            $this->destroyExistingShares();
            foreach ($this->virtualizerfile->config["vm"]["shared_folders"] as $sharedFolder) {
                $logging->log("Adding Shared Folder named {$sharedFolder["name"]} to VM {$this->virtualizerfile->config["vm"]["name"]} to Host path {$sharedFolder["host_path"]}") ;
                $this->provider->addShare($this->virtualizerfile->config["vm"]["name"], $sharedFolder); } }
    }


    protected function destroyExistingShares() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Finding existing shares") ;
        $names = $this->provider->getShares($this->virtualizerfile->config["vm"]["name"]);
        foreach ($names as $share) {
            $logging->log("Removing Shared Folder named {$share} from VM {$this->virtualizerfile->config["vm"]["name"]}") ;
            $this->provider->removeShare($this->virtualizerfile->config["vm"]["name"], $share); }
    }

    protected function modifyHardDisks() {
        $this->loadFiles();
        $this->findProvider("UpModify");
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Virtualizerfile specifies Resizing HD for VM {$this->virtualizerfile->config["vm"]["name"]}") ;
        $logging->log("Finding existing hard disks") ;
        $disks = $this->provider->getDisks($this->virtualizerfile->config["vm"]["name"]);
        foreach ($disks as $disk) {
            $logging->log("Modifying HD $disk system by changing size to {$this->virtualizerfile->config["vm"]["hd_resize"]}") ;
            $this->provider->modifyDisk($disk, $this->virtualizerfile->config["vm"]["hd_resize"]); }
    }

}