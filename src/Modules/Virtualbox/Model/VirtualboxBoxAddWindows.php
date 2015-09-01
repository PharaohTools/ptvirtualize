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
            $logging->log("Files already exist at $boxdir. Cannot create directory to add box.", $this->getModuleName()) ;
            return null; }
        if (!file_exists($target)) {
            $logging->log("Adding parent box directory $target.", $this->getModuleName()) ;
            $command = "mkdir $boxdir" ;
            self::executeAndOutput($command);}
        return $boxdir ;
    }

    protected function extractMetadata($source, $boxDir) {
        // @todo needs vagrant update from linux version
        $boxFile = $source ;
        $tarExe = '"'.dirname(dirname(dirname(__FILE__))).'\Tar\Packages\TarGnu\bin\Tar.exe"' ;
        $start_directory = getcwd() ;
        chdir(BASE_TEMP_DIR) ;
        if (!file_exists($boxDir)) {
            $command = "mkdir \"$boxDir\"" ;
            self::executeAndOutput($command);}
        $drivelessBoxFile = substr($boxFile, 2) ;
        $command = "$tarExe --extract --file=\"$drivelessBoxFile\" ./metadata.json" ;
        self::executeAndOutput($command);
        $command = "move ".BASE_TEMP_DIR."metadata.json $boxDir" ;
        self::executeAndOutput($command);
        chdir($start_directory) ;
    }

    protected function findOVA($source) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Finding ova file name from box file...", $this->getModuleName()) ;
        $tarExe = '"'.dirname(dirname(dirname(__FILE__))).'\Tar\Packages\TarGnu\bin\Tar.exe"' ;
        $boxFile = $source ;
        $start_directory = getcwd() ;
        chdir(BASE_TEMP_DIR) ;
        $drivelessBoxFile = substr($boxFile, 2) ;
        $command = "$tarExe -tf \"$drivelessBoxFile\"" ;
        $eachFileRay = explode("\n", self::executeAndLoad($command));
        chdir($start_directory) ;
        foreach ($eachFileRay as $oneFile) {
//            $fileExt = substr($oneFile, -4) ;
            if (strpos($oneFile, ".ova")!==false || strpos($oneFile, ".ovf")!==false) {
                $lpos = strpos($oneFile, "./") ;
                $fname = substr($oneFile, $lpos) ;
                $fname = str_replace("./", "", $fname) ;
                $fname = str_replace(".\\", "", $fname) ;
                $logging->log("Found ova file $fname from box file...", $this->getModuleName()) ;
                return $fname ; } }
        return null ;
    }

    protected function extractAll($source, $boxDir) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Extracting all files from box file...", $this->getModuleName()) ;
        $tarExe = '"'.dirname(dirname(dirname(__FILE__))).'\Tar\Packages\TarGnu\bin\Tar.exe"' ;
        if (!file_exists($boxDir)) {
            $command = "mkdir \"$boxDir\"" ;
            self::executeAndOutput($command); }
        $start_directory = getcwd() ;
        mkdir(BASE_TEMP_DIR.DS."extr") ;
        chdir(BASE_TEMP_DIR."extr") ;
        $csource = substr($source, 2) ;
        $command = "$tarExe --extract --file=\"$csource\" ./*" ;
        self::executeAndOutput($command);
        $command = "move ".BASE_TEMP_DIR.DS."extr".DS."* \"$boxDir\"" ;
        self::executeAndOutput($command);
        $logging->log("Extraction complete...", $this->getModuleName()) ;
        chdir($start_directory) ;
    }

    protected function changeOVAName($boxDir, $ovaFile) {
        if ($ovaFile != "box.ova") {
            $loggingFactory = new \Model\Logging();
            $logging = $loggingFactory->getModel($this->params) ;
            $logging->log("Changing ova file name from $ovaFile to box.ova...", $this->getModuleName());
            $command = "rename $boxDir".DS."$ovaFile $boxDir".DS."box.ova" ;
            self::executeAndOutput($command); }
    }

}