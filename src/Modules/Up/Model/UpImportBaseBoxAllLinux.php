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
        $baseBoxPath = $this->findBaseBox();
        // if its null, we don't have the box yet, so box add it
        if (is_null($baseBoxPath)) {
            $logging->log("Base Box {$this->phlagrantfile->config["vm"]["box"]} doesn't exist locally, adding...") ;
            // @lol simpsons below
            $boxFactory = new \Model\Box();
            $boxParams = $this->params ;
            $boxParams["source"] = $this->getRemoteSource() ;
            $boxParams["guess"] = true ; // guess target
            if (strpos($this->phlagrantfile->config["vm"]["box"], "/") != false) {
                $name = substr($this->phlagrantfile->config["vm"]["box"], strpos($this->phlagrantfile->config["vm"]["box"], "/")) ;
                $logging->log("Guessing name $name ...") ; }
            else {
                $name = substr($this->phlagrantfile->config["vm"]["box"], strpos($this->phlagrantfile->config["vm"]["box"], "/")) ;
                $logging->log("Guessing name $name ...") ; }
            $boxParams["name"] = $name ;
            $box = $boxFactory->getModel($boxParams) ;
            $box->performBoxAdd() ;
            $baseBoxPath = $this->findBaseBox(); }
        $ovaFile = $this->findOVAFile($baseBoxPath) ;
        $out = $this->doImport($ovaFile) ;
        return $out ;
    }

    protected function getRemoteSource() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $home_url = "http://www.phlagrantboxes.co.uk/" ;
        if (isset($this->phlagrantfile->config["vm"]["box_url"])) {
            $source = $this->phlagrantfile->config["vm"]["box_url"] ;
            $logging->log("Using explicit Box URL {$this->phlagrantfile->config["vm"]["box_url"]} from Phlagrantfile...") ; }
        else if (strpos($this->phlagrantfile->config["vm"]["box"], "/") != false) {
            $source = $home_url.$this->phlagrantfile->config["vm"]["box"] ;
            $logging->log("Guessing Box URL {$home_url}{$this->phlagrantfile->config["vm"]["box"]} ...") ; }
        else {
            $source = $home_url.'phlagrant/'.$this->phlagrantfile->config["vm"]["box"] ;
            // @todo dont DS this its a URL
            $logging->log("Guessing Box URL {$home_url}phlagrant/{$this->phlagrantfile->config["vm"]["box"]} ...") ; }
        return $source ;
    }

    protected function findBaseBox() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Finding base box {$this->phlagrantfile->config["vm"]["box"]} from Phlagrantfile") ;
        // @todo get rid of this hardcode
        $dirscan = '/opt/phlagrant/boxes' ;
        $filesInDir = scandir($dirscan) ;
        $boxes = array() ;
        foreach ($filesInDir as $fileInDir) {
            if (in_array($fileInDir, array(".", ".."))) { continue ; }
            if (is_dir($dirscan.'/'.$fileInDir)) { $boxes[] = $fileInDir ; } }
        foreach ($boxes as $box) {
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