<?php

Namespace Model;

class UpImportBaseBoxAllLinux extends BaseLinuxApp {

    // Compatibility
    public $os = array("Linux") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("ImportBaseBox") ;

    public $papyrus;
    public $phlagrantfile;

    public function performImport() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Importing Base Box...") ;

        // finding base box $this->phlagrantfile->config["vm"]["name"]} from Phlagrantfile
        // find ova file in there
        // run import command to  $this->phlagrantfile->config["vm"]["name"]}

        $baseBoxPath = $this->findBaseBox();
        $ovaFile = $this->findOVAFile($baseBoxPath) ;
        $out = $this->doImport($ovaFile) ;
    }

    protected function findBaseBox() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Finding base box {$this->phlagrantfile->config["vm"]["box"]} from Phlagrantfile") ;
        // @todo get rid of this hardcode
        $dirscan = '/opt/phlagrant/boxes' ;
        $boxes = scandir($dirscan) ;
        foreach ($boxes as $box) {
//            echo "box: $box\n" ;
//            echo "cbox: {$this->phlagrantfile->config["vm"]["box"]}\n" ;
            if ($box == $this->phlagrantfile->config["vm"]["box"]) {
                $logging->log("Found base box {$box}") ;
                return $dirscan.'/'.$box ; } }
        return null ;
    }

    protected function findOVAFile($baseBox) {
        $ovaFile = $baseBox.'/box.ova' ;
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Finding OVA file $ovaFile") ;
        if (file_exists($ovaFile)) {
            $logging->log("Found OVA file {$ovaFile}") ;
            return $ovaFile ; }
        return null ;
    }

    protected function doImport($ovaFile) {
        $command  = "vboxmanage import {$ovaFile} --vsys 0 --ostype {$this->phlagrantfile->config["vm"]["ostype"]}" ;
        $command .= " --vmname {$this->phlagrantfile->config["vm"]["name"]}" ;
        $this->executeAndOutput($command);
        return true ;
    }

}