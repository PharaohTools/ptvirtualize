<?php

Namespace Model;

class VirtualboxBoxAddWindows extends VirtualboxBoxAddLinuxMac {

    // Compatibility
    public $os = array("Windows", "WINNT") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("BoxAdd") ;

    protected function createBoxDirectory($target, $name) {
        $boxdir = $target . $name ;
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (file_exists($boxdir)) {
            $logging->log("Files already exist at $boxdir. Cannot create directory to add box.");
            return null; }
        if (!file_exists($target)) {
            $logging->log("Adding parent box directory $target.");
            $command = "mkdir $boxdir" ;
            self::executeAndOutput($command);}
        return $boxdir ;
    }

    protected function extractMetadata($source, $boxDir) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Extracting metadata.json from box file...");
        $pd = new \PharData($source) ;
        $pd->extractTo($boxDir."metadata.json", "metadata.json", true) ;
    }

    protected function findOVA($source) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Finding ova file name from box file...") ;
        $tar = new \Phar($source, 0);
        $eachFile = array() ;
        foreach (new \RecursiveIteratorIterator($tar) as $file) {
            $eachFile[] = $file->getFileName() ; }
        foreach ($eachFile as $oneFile) {
            $fileExt = substr($oneFile, -4) ;
            if ($fileExt == ".ova" || $fileExt ==".ovf") {
                $stripped = str_replace("./", "", $oneFile) ;
                $stripped = str_replace(".\\", "", $stripped) ;
                $logging->log("Found ova file $stripped from box file...");
                return $stripped ; } }
        return null ;
    }

    protected function extractOVA($source, $boxDir, $ovaFile) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Extracting ova file $ovaFile from box file...");
        $tar = new \Phar($source);

        $tar->extractTo($boxDir, $ovaFile); // extract only file.txt

        //$command = "tar --extract --file=$source -C $boxDir ./$ovaFile" ;
        //self::executeAndOutput($command);
        $logging->log("Extraction complete...");
    }

    protected function changeOVAName($boxDir, $ovaFile) {
        if ($ovaFile != "box.ova") {
            $loggingFactory = new \Model\Logging();
            $logging = $loggingFactory->getModel($this->params) ;
            $logging->log("Changing ova file name from $ovaFile to box.ova...");
            $command = "rename $boxDir".DS."$ovaFile $boxDir".DS."box.ova" ;
            self::executeAndOutput($command); }
    }

}