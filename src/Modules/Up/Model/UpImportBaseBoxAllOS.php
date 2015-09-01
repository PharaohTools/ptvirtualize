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
        $logging->log("Importing Base Box...", $this->getModuleName()) ;
        $baseBoxPath = $this->findBaseBox();
        // if its null, we don't have the box yet, so box add it
        if (is_null($baseBoxPath)) {
            $logging->log("Base Box {$this->virtufile->config["vm"]["box"]} doesn't exist locally, adding...", $this->getModuleName()) ;
            $boxFactory = new \Model\Box();
            $boxParams = $this->params ;
            $boxParams["source"] = $this->getRemoteSource() ;
            $boxParams["guess"] = true ; // guess target
            if (strpos($this->virtufile->config["vm"]["box"], "/") != false) {
                $name = substr($this->virtufile->config["vm"]["box"], strpos($this->virtufile->config["vm"]["box"], "/")) ;
                $logging->log("Guessing name $name ...", $this->getModuleName()) ; }
            else {
                $name = substr($this->virtufile->config["vm"]["box"], strpos($this->virtufile->config["vm"]["box"], "/")) ;
                $logging->log("Guessing name $name ...", $this->getModuleName()) ; }
            $boxParams["name"] = $name ;
            $box = $boxFactory->getModel($boxParams) ;
            if (!is_object($box)) {
                $logging->log("No Box model available for this system ...", $this->getModuleName()) ;
                return false ; }
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
        $home_url = "http://www.pharaohtools.com/virtualize/boxes/" ;
        if (isset($this->virtufile->config["vm"]["box_url"])) {
            $source = $this->virtufile->config["vm"]["box_url"] ;
            $logging->log("Using explicit Box URL {$this->virtufile->config["vm"]["box_url"]} from Virtufile...", $this->getModuleName()) ; }
        else if (strpos($this->virtufile->config["vm"]["box"], "/") != false) {
            $source = $home_url.$this->virtufile->config["vm"]["box"] ;
            $logging->log("Guessing Box URL {$home_url}{$this->virtufile->config["vm"]["box"]} ...", $this->getModuleName()) ; }
        else {
            $source = $home_url.''.$this->virtufile->config["vm"]["box"] ;
            $logging->log("Guessing Box URL {$home_url}{$this->virtufile->config["vm"]["box"]} ...", $this->getModuleName()) ; }
        return $source ;
    }

    protected function findBaseBox() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Finding base box {$this->virtufile->config["vm"]["box"]} from Virtufile", $this->getModuleName()) ;
        $dirscan = BOXDIR ;
        if (file_exists(BOXDIR)) {
            $logging->log("Found base box directory ".BOXDIR, $this->getModuleName()) ;
            $filesInDir = scandir(BOXDIR) ;
            $boxes = array() ;
            foreach ($filesInDir as $fileInDir) {
                if (in_array($fileInDir, array(".", ".."))) { continue ; }
                if (is_dir($dirscan.DS.$fileInDir)) {
                    $boxes[] = $fileInDir ; } }
            foreach ($boxes as $box) {
                $confBox = $this->virtufile->config["vm"]["box"] ;
                // @todo for if we are looking for box archive files also
//                if (substr($this->virtufile->config["vm"]["box"], strlen($this->virtufile->config["vm"]["box"])-4) !== ".box"){
//                    $confBox .= ".box" ; }
//                var_dump("a1", $confBox, $box) ;
                if ($box == $this->virtufile->config["vm"]["box"]) {
                    $logging->log("Found base box {$box}", $this->getModuleName()) ;
                    return $dirscan.DS.$box ; } } }
        else {
            $logging->log("No base box directory ".BOXDIR, $this->getModuleName()) ; }
        return null ;
    }

    protected function findOVAFile($baseBox) {
        $ovaFile = $baseBox.DS.'box.ova' ;
        $ovfFile = $baseBox.DS.'box.ovf' ;
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Finding OVA file $ovaFile", $this->getModuleName()) ;
        if (file_exists($ovaFile)) {
            $logging->log("Found OVA file {$ovaFile}", $this->getModuleName()) ;
            return $ovaFile ; }
        if (file_exists($ovfFile)) {
            $logging->log("Found OVF file {$ovfFile}", $this->getModuleName()) ;
            return $ovfFile ; }
        return null ;
    }

    protected function doImport($ovaFile) {
        $this->loadFiles();
        $this->findProvider("UpImport");
        return $this->provider->import($ovaFile, $this->virtufile->config["vm"]["ostype"], $this->virtufile->config["vm"]["name"]);
    }

}