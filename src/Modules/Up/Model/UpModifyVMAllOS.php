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
        // @todo we should return false if things dont work
        $this->loadFiles();
        $this->findProvider("UpModify");
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $this->setAvailableModifications();
        if (isset($this->virtufile->config["vm"]["hd_resize"])) {
            $this->modifyHardDisks(); }
        foreach ($this->virtufile->config["vm"] as $configKey => $configValue) {
            if (in_array($configKey, $this->availableModifications)) {
                $logging->log("Modifying VM {$this->virtufile->config["vm"]["name"]} system by changing $configKey to $configValue", $this->getModuleName()) ;
                $this->provider->modify($this->virtufile->config["vm"]["name"], $configKey, $configValue); } }
        $this->setAvailableNetworkModifications();
        foreach ($this->virtufile->config["network"] as $configKey => $configValue) {
            if (!is_array($configValue)) {
                $configValue = array($configValue) ;}
            foreach ($configValue as $configVal) {

                if (in_array($configKey, $this->availableNetworkModifications)) {
                    $logging->log("Modifying VM {$this->virtufile->config["vm"]["name"]} network by changing $configKey to $configVal", $this->getModuleName()) ;
                    $this->provider->modify($this->virtufile->config["vm"]["name"], $configKey, $configVal); }
            } }
        $this->setSharedFolders();
        return true ;
    }

    public function removeShares() {
        $this->loadFiles();
        $this->findProvider("UpModify");
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Removing shared folders", $this->getModuleName()) ;
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
        if (isset($this->virtufile->config["vm"]["shared_folders"]) && count($this->virtufile->config["vm"]["shared_folders"])>0 ) {
            $this->destroyExistingShares();
            foreach ($this->virtufile->config["vm"]["shared_folders"] as $sharedFolder) {
                $logging->log("Adding Shared Folder named {$sharedFolder["name"]} to VM {$this->virtufile->config["vm"]["name"]} to Host path {$sharedFolder["host_path"]}", $this->getModuleName()) ;
                $this->provider->addShare($this->virtufile->config["vm"]["name"], $sharedFolder); } }
    }


    protected function destroyExistingShares() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Finding existing shares", $this->getModuleName()) ;
        $names = $this->provider->getShares($this->virtufile->config["vm"]["name"]);
        foreach ($names as $share) {
            $logging->log("Removing Shared Folder named {$share} from VM {$this->virtufile->config["vm"]["name"]}", $this->getModuleName()) ;
            $this->provider->removeShare($this->virtufile->config["vm"]["name"], $share); }
    }

    protected function modifyHardDisks() {
        $this->loadFiles();
        $this->findProvider("UpModify");
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Virtufile specifies Resizing HD for VM {$this->virtufile->config["vm"]["name"]}", $this->getModuleName()) ;
        $logging->log("Finding existing hard disks", $this->getModuleName()) ;
        $disks = $this->provider->getDisks($this->virtufile->config["vm"]["name"]);
        foreach ($disks as $disk) {
            $logging->log("Modifying HD $disk system by changing size to {$this->virtufile->config["vm"]["hd_resize"]}", $this->getModuleName()) ;
            $this->provider->modifyDisk($disk, $this->virtufile->config["vm"]["hd_resize"]); }
    }

}