<?php

Namespace Model;

class UpImportBaseBoxAllOS extends BaseFunctionModel {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("ImportBaseBox") ;

    public function performImport() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Importing Base Box...") ;
        $baseBoxPath = $this->findBaseBox();
        // if its null, we don't have the box yet, so box add it
        if (is_null($baseBoxPath)) {
            $logging->log("Base Box {$this->virtufile->config["vm"]["box"]} doesn't exist locally, adding...") ;
            $boxFactory = new \Model\Box();
            $boxParams = $this->params ;
            $boxParams["source"] = $this->getRemoteSource() ;
            $boxParams["guess"] = true ; // guess target
            if (strpos($this->virtufile->config["vm"]["box"], "/") != false) {
                $name = substr($this->virtufile->config["vm"]["box"], strpos($this->virtufile->config["vm"]["box"], "/")) ;
                $logging->log("Guessing name $name ...") ; }
            else {
                $name = substr($this->virtufile->config["vm"]["box"], strpos($this->virtufile->config["vm"]["box"], "/")) ;
                $logging->log("Guessing name $name ...") ; }
            $boxParams["name"] = $name ;
            $box = $boxFactory->getModel($boxParams) ;
            $res = $box->performBoxAdd() ;
            if ($res == false) {
                return false; }
            $baseBoxPath = $this->findBaseBox(); }
        $ovaFile = $this->findOVAFile($baseBoxPath) ;
        $out = $this->doImport($ovaFile) ;
        return $out ;
    }

    protected function getRemoteSource() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $home_url = "http://www.pharaohtools.com/ptvirtualize/boxes/" ;
        if (isset($this->virtufile->config["vm"]["box_url"])) {
            $source = $this->virtufile->config["vm"]["box_url"] ;
            $logging->log("Using explicit Box URL {$this->virtufile->config["vm"]["box_url"]} from Virtufile...") ; }
        else if (strpos($this->virtufile->config["vm"]["box"], "/") != false) {
            $source = $home_url.$this->virtufile->config["vm"]["box"] ;
            $logging->log("Guessing Box URL {$home_url}{$this->virtufile->config["vm"]["box"]} ...") ; }
        else {
            $source = $home_url.''.$this->virtufile->config["vm"]["box"] ;
            $logging->log("Guessing Box URL {$home_url}{$this->virtufile->config["vm"]["box"]} ...") ; }
        return $source ;
    }

    protected function findBaseBox() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Finding base box {$this->virtufile->config["vm"]["box"]} from Virtufile") ;
        $dirscan = BOXDIR ;
        if (file_exists(BOXDIR)) {
            $logging->log("Found base box directory ".BOXDIR) ;
            $filesInDir = scandir(BOXDIR) ;
            $boxes = array() ;
            foreach ($filesInDir as $fileInDir) {
                if (in_array($fileInDir, array(".", ".."))) { continue ; }
                if (is_dir($dirscan.DS.$fileInDir)) { $boxes[] = $fileInDir ; } }
            foreach ($boxes as $box) {
                if ($box.".box" == $this->virtufile->config["vm"]["box"]) {
                    $logging->log("Found base box {$box}") ;
                    return $dirscan.DS.$box ; } } }
        else {
            $logging->log("No base box directory ".BOXDIR) ; }
        return null ;
    }

    protected function findOVAFile($baseBox) {
        $ovaFile = $baseBox.DS.'box.ova' ;
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Finding OVA file $ovaFile") ;
        if (file_exists($ovaFile)) {
            $logging->log("Found OVA file {$ovaFile}") ;
            return $ovaFile ; }
        return null ;
    }

    protected function doImport($ovaFile) {
        $this->loadFiles();
        $this->findProvider("UpImport");
        return $this->provider->import($ovaFile, $this->virtufile->config["vm"]["ostype"], $this->virtufile->config["vm"]["name"]);
    }

}