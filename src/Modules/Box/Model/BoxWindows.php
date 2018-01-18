<?php

Namespace Model;

class BoxWindows extends BoxLinuxMac {

    // Compatibility
    public $os = array("Windows", "WINNT") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    public function __construct($params) {
        parent::__construct($params);
    }

    protected function extractMetadata() {
        $boxFile = $this->source ;
        $tarExe = '"'.dirname(dirname(dirname(__FILE__))).'\Tar\Packages\TarGnu\bin\Tar.exe"' ;
        $start_directory = getcwd() ;
        chdir(BASE_TEMP_DIR) ;
        // $boxFile = str_replace(BASE_TEMP_DIR, "", $boxFile) ;
        $drivelessBoxFile = substr($boxFile, 2) ;
        $command = "$tarExe --extract --file=\"$drivelessBoxFile\" ./metadata.json" ;
        self::executeAndOutput($command);
        $fData = file_get_contents(BASE_TEMP_DIR."metadata.json") ;
        $command = "del ".BASE_TEMP_DIR."metadata.json" ;
        self::executeAndOutput($command);
        $fdo = json_decode($fData) ;
        chdir($start_directory) ;
        if (is_object($fdo)) { return $fdo ; }
        return false ;
    }
}