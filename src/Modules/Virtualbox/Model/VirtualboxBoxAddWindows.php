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
        $boxFile = $source ;
        $tarExe = '"'.dirname(dirname(dirname(__FILE__))).'\Tar\Packages\TarGnu\bin\Tar.exe"' ;
        chdir("C:\\Temp") ;
         $boxFile = str_replace("C:\\Temp\\", "", $boxFile) ;
        if (!file_exists($boxDir)) {
            $command = "mkdir \"$boxDir\"" ;
            self::executeAndOutput($command);}
        $command = "$tarExe --extract --file=\"$boxFile\" ./metadata.json" ;
        self::executeAndOutput($command);
        $command = "move ".BASE_TEMP_DIR."metadata.json $boxDir" ;
        self::executeAndOutput($command);
    }

    protected function findOVA($source) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Finding ova file name from box file...") ;
        $tarExe = '"'.dirname(dirname(dirname(__FILE__))).'\Tar\Packages\TarGnu\bin\Tar.exe"' ;
        chdir("C:\\Temp") ;
        $boxFile = str_replace("C:\\Temp\\", "", $source) ;
        $command = "$tarExe -tvf \"$boxFile\"" ;
        $eachFileRay = explode("\n", self::executeAndLoad($command));
        foreach ($eachFileRay as $oneFile) {
            $fileExt = substr($oneFile, -4) ;
            if (strpos($oneFile, ".ova")!==false || strpos($oneFile, ".ovf")!==false) {
                $lpos = strpos($oneFile, "./") ;
                $fname = substr($oneFile, $lpos) ;
                $fname = str_replace("./", "", $fname) ;
                $fname = str_replace(".\\", "", $fname) ;
                $logging->log("Found ova file $fname from box file...");
                return $fname ; } }
        return null ;
    }

    protected function extractOVA($source, $boxDir, $ovaFile) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Extracting ova file $ovaFile from box file...");
        $tarExe = '"'.dirname(dirname(dirname(__FILE__))).'\Tar\Packages\TarGnu\bin\Tar.exe"' ;
        if (!file_exists($boxDir)) {
            $command = "mkdir \"$boxDir\"" ;
            self::executeAndOutput($command);}
        chdir("C:\\Temp\\") ;
        $csource = substr($source, 8) ;
        $command = "$tarExe --extract --file=\"$csource\" ./$ovaFile" ;
        self::executeAndOutput($command);
        $command = "move ".BASE_TEMP_DIR."$ovaFile \"$boxDir\"" ;
        self::executeAndOutput($command);
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