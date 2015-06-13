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

    protected function downloadIfRemote() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (substr($this->source, 0, 7) == "http://" || substr($this->source, 0, 8) == "https://") {
            $this->source = $this->ensureTrailingSlash($this->source);
            $logging->log("Box is remote not local, will download to temp directory before adding...", $this->getModuleName()) ;
            set_time_limit(0); // unlimited max execution time
            $tmpFile = BASE_TEMP_DIR.'file.box' ;

            $logging->log("Mock Downloading File ...", $this->getModuleName()) ;

//            $logging->log("Downloading File ...");
//            if (substr($this->source, strlen($this->source)-1, 1) == DS) {
//                $this->source = substr($this->source, 0, strlen($this->source)-1) ; }
//            // @todo error return false
//            $wgetExe = '"'.dirname(dirname(dirname(__FILE__))).'\WgetWin\Packages\WgetWin\wget.exe"' ;
//            $comm = "$wgetExe -O $tmpFile {$this->source}" ;
//            $rt = self::executeAndGetReturnCode($comm, true, true) ;
//            if ($rt["rc"] !== 0) {
//                $logging->log("File Download Failed", $this->getModuleName());
//                return false; }

            $this->source = $tmpFile ;
            $logging->log("Download complete ...", $this->getModuleName());
            return true ;}
        return true ;
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