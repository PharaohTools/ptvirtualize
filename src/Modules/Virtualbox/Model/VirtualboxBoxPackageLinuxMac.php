<?php

Namespace Model;

class VirtualboxBoxPackageLinuxMac extends BaseVirtualboxAllOS {

    // Compatibility
    public $os = array("Linux", "Darwin") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("BoxPackage") ;

    public function packageBox($target, $vmName, $metadata) {
        // package the box here
        // create the directory for the box
        $boxDir = $this->createTempDirectory($metadata) ;
        if (!is_null($boxDir)) {
            // put the metadata file in the temp box directory
            $this->saveMetadataToFS($metadata) ;
            // export the ova there too
            $this->exportOVA($vmName, $metadata) ;
            // tar both to the target directory
            $this->createPackage($target, $metadata) ;
            $this->completion() ;
        }
    }

    protected function askForBoxPackageExecute() {
        if (isset($this->params["yes"]) && $this->params["yes"]==true) { return true ; }
        $question = 'Package Virtualbox Server Boxes?';
        return self::askYesOrNo($question);
    }

    protected function saveMetadataToFS($metadata) {
        $file = BASE_TEMP_DIR.DS."ptvirtualize".DS.$metadata->slug.DS."metadata.json" ;
        $string = json_encode($metadata) ;
        file_put_contents($file, $string) ;
    }

    protected function createTempDirectory($metadata) {
        $boxdir = BASE_TEMP_DIR.'ptvirtualize'.DS . $metadata->slug ;
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (file_exists($boxdir) && !is_writable($boxdir)) {
            $logging->log("Directory $boxdir exists and is not writable. Removing.");
            $command = "sudo rm -rf $boxdir" ;
            self::executeAndOutput($command);
            $command = "mkdir -p $boxdir" ;
            self::executeAndOutput($command);
            return $boxdir; }
        if (file_exists($boxdir)) {
            $logging->log("Files already exist at $boxdir. Removing.");
            $command = "sudo rm -rf $boxdir" ;
            self::executeAndOutput($command);
            $command = "mkdir -p $boxdir" ;
            self::executeAndOutput($command);
            return $boxdir; }
        else {
            $logging->log("Creating $boxdir");
            $command = "mkdir -p $boxdir" ;
            self::executeAndOutput($command);
            return $boxdir ; }
    }

    protected function exportOVA($vmName, $metadata) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Exporting ova file box.ova from Virtual Machine $vmName...");
        $command = VBOXMGCOMM." export {$vmName} --output=/tmp/ptvirtualize/{$metadata->slug}/box.ova" ;
        self::executeAndOutput($command);
        $logging->log("Export complete...");
    }

    protected function createPackage($target, $metadata) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Creating box file from ova file and json file...");
        $command = "tar -cvf $target$metadata->slug.box -C /tmp/ptvirtualize/{$metadata->slug} . " ;
        self::executeAndOutput($command);
        $logging->log("Created box file $target{$metadata->slug}.box...");
        return true ;
    }

    protected function completion() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Completed Packaging Box...");
    }

}