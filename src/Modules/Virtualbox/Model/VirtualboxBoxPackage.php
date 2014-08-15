<?php

Namespace Model;

class VirtualboxBoxPackage extends BaseVirtualboxAllOS {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("BoxPackage") ;

    public function packageBox($target, $vmName, $packageName, $metadata) {
        // package the box here
        // create the directory for the box
        $boxDir = $this->createTempDirectory($packageName) ;
        if (!is_null($boxDir)) {
            // put the metadata file in the temp box directory
            $this->saveMetadataToFS($packageName, $metadata) ;
            // export the ova there too
            $this->exportOVA($vmName, $packageName) ;
            // tar both to the target directory
            $this->createPackage($target, $packageName) ;
            $this->completion() ;
        }
    }

    protected function askForBoxPackageExecute() {
        if (isset($this->params["yes"]) && $this->params["yes"]==true) { return true ; }
        $question = 'Package Virtualbox Server Boxes?';
        return self::askYesOrNo($question);
    }

    protected function saveMetadataToFS($name, $metadata) {
        $file = "/tmp/phlagrant/{$name}/metadata.json" ;
        $string = json_encode($metadata) ;
        file_put_contents($file, $string) ;
    }

    protected function createTempDirectory($name) {
        $boxdir = '/tmp/phlagrant/' . $name ;
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

    protected function exportOVA($vmName, $packageName) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Exporting ova file box,ova from Virtual Machine $vmName...");
        $command = "vboxmanage export {$vmName} --output=/tmp/phlagrant/{$packageName}/box.ova" ;
        self::executeAndOutput($command);
        $logging->log("Export complete...");
    }

    protected function createPackage($target, $packageName) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Creating box file from ova file and json file...");
        $command = "tar -cvf $target/$packageName.box -C /tmp/phlagrant/{$packageName} . " ;
        self::executeAndOutput($command);
        $logging->log("Created box file $target/$packageName.box...");
        return true ;
    }

    protected function completion() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Completed Packaging Box...");
    }

}