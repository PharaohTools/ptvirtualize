<?php

Namespace Model;

class BoxWindows extends BoxUbuntu {

    // Compatibility
    public $os = array("Windows", "WINNT") ;
    public $linuxType = array("None") ;
    public $distros = array("None") ;
    public $versions = array(array("5.0" => "+")) ;
    public $architectures = array("any") ;

    public function __construct($params) {
        parent::__construct($params);
    }

    protected function downloadIfRemote() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (substr($this->source, 0, 7) == "http://" || substr($this->source, 0, 8) == "https://") {
            $this->source = $this->ensureTrailingSlash($this->source);
            $logging->log("Box is remote not local, will download to temp directory before adding...");
            set_time_limit(0); // unlimited max execution time
            $tmpFile = BASE_TEMP_DIR.'file.box' ;
            $logging->log("Downloading File ...");
            if (substr($this->source, strlen($this->source)-1, 1) == DS) {
                $this->source = substr($this->source, 0, strlen($this->source)-1) ; }
            // @todo error return false
            $wgetExe = '"'.dirname(dirname(dirname(__FILE__))).'\WgetWin\Packages\WgetWin\wget.exe"' ;
            $comm = "$wgetExe -O $tmpFile {$this->source}" ;
            self::executeAndOutput($comm) ;
            $this->source = $tmpFile ;
            $logging->log("Download complete ...");
            return true ;}
        return true ;
    }

    protected function extractMetadata() {
        $pd = new \PharData($this->source) ;
        $pd->extractTo(BASE_TEMP_DIR, "metadata.json", true) ;
        $fData = file_get_contents(BASE_TEMP_DIR."metadata.json") ;
        $fdo = json_decode($fData) ;
        $command = "del ".BASE_TEMP_DIR."metadata.json" ;
        self::executeAndOutput($command);
        return $fdo ;
    }

}