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
            $logging->log("Box is remote not local, will download to temp directory before adding...", $this->getModuleName()) ;
            set_time_limit(0); // unlimited max execution time
            $tmpFile = BASE_TEMP_DIR.'file.box' ;
            $res = $this->packageDownload($this->source, $tmpFile) ;
            if ($res == true) {
                $this->source = $tmpFile ;
                $logging->log("Download complete ...", $this->getModuleName());
                return true ;
            } else {
                $logging->log("File Download Failed", $this->getModuleName());
                return false; } }
        $logging->log("File Path is local not remote, no download required", $this->getModuleName());
        return true ;
    }

    public function packageDownload($remote_source, $temp_exe_file) {
        if (file_exists($temp_exe_file)) {
            unlink($temp_exe_file) ;
        }
        # var_dump('BWA packageDownload 2', $_ENV, $temp_exe_file) ;
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $logging->log("Downloading From {$remote_source}", $this->getModuleName() ) ;

        echo "Download Starting ...".PHP_EOL;
        ob_start();
        ob_flush();
        flush();

        $fp = fopen ($temp_exe_file, 'w') ;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remote_source);
        // curl_setopt($ch, CURLOPT_BUFFERSIZE,128);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, array($this, 'progress'));
        curl_setopt($ch, CURLOPT_NOPROGRESS, false); // needed to make progress function work
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $downloaded = curl_exec($ch);
        # $error = curl_error($ch) ;
        # var_dump('downloaded', $downloaded, $error) ;
        fwrite($fp, $downloaded) ;
        curl_close($ch);

        ob_flush();
        flush();

        echo "Done".PHP_EOL ;
        return $temp_exe_file ;
    }

    public function progress($resource, $download_size, $downloaded, $upload_size, $uploaded) {
        $is_quiet = (isset($this->params['quiet']) && ($this->params['quiet'] == true) ) ;
        if ($is_quiet == false) {
            if($download_size > 0) {
                $dl = ($downloaded / $download_size)  * 100 ;
                # var_dump('downloaded', $dl) ;
                $perc = round($dl, 2) ;
                # var_dump('perc', $perc) ;
                echo "{$perc} % \r" ;
            }
            ob_flush();
            flush();
        }
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