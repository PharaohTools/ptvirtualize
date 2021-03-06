<?php

Namespace Model;

class VirtualboxBoxPackageWindows extends VirtualboxBoxPackageLinuxMac {

    // Compatibility
    public $os = array("Windows", "WINNT") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("BoxPackage") ;

    protected function createTempDirectory($metadata) {
        $boxdir = BASE_TEMP_DIR.'ptvirtualize'.DS.$metadata->slug ;
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (file_exists($boxdir) && !is_writable($boxdir)) {
            $logging->log("Directory $boxdir exists and is not writable. Removing.", $this->getModuleName());
            $command = "del /S /Q $boxdir" ;
            self::executeAndOutput($command);
            $command = "mkdir $boxdir" ;
            self::executeAndOutput($command);
            return $boxdir; }
        if (file_exists($boxdir)) {
            $logging->log("Files already exist at $boxdir. Removing.", $this->getModuleName());
            $command = "del /S /Q $boxdir" ;
            self::executeAndOutput($command);
            $command = "mkdir $boxdir" ;
            self::executeAndOutput($command);
            return $boxdir; }
        else {
            $logging->log("Creating $boxdir", $this->getModuleName());
            $command = "mkdir $boxdir" ;
            self::executeAndOutput($command);
            return $boxdir ; }
    }

    protected function createPackage($target, $metadata) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $modroot = dirname(dirname(__DIR__)) ;
        $exepath = $modroot . '\Tar\Packages\TarGnu\bin\tar.exe';
        $logging->log("Creating box file from ova file and json file...", $this->getModuleName());
        // @note the driveless target is necessary when specifying the file target
        $drivelessTarget = substr($target, 2) ;
        $command = '"'.$exepath.'"'." -c -v -f {$drivelessTarget}{$metadata->slug}.box -C ".BASE_TEMP_DIR.DS.
            "ptvirtualize".DS.$metadata->slug." . " ;
        // echo "\n".$command."\n\n" ;
        self::executeAndOutput($command);
        $logging->log("Created box file $target{$metadata->slug}.box...", $this->getModuleName());
        return true ;
    }

}