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
            if (substr($this->source, strlen($this->source)-1, 1) == '/') {
                $this->source = substr($this->source, 0, strlen($this->source)-1) ; }
            // @todo error return false
            $this->downloadCall($this->source, $tmpFile) ;
            $this->source = $tmpFile ;
            $logging->log("Download complete ...");
            return true ;}
        return true ;
    }

    protected function downloadCall($source, $tmpFile) {

        $ctx = stream_context_create();
        stream_context_set_params($ctx, array("notification" => "stream_notification_callback"));

        $fp = fopen($source, "r", false, $ctx);
        if (is_resource($fp) && file_put_contents($tmpFile, $fp)) {
            echo "\nDone!\n";
            return true; }

        $err = error_get_last();
        echo "\nError..\n", $err["message"], "\n";
        return false ;
    }

    protected function extractMetadata() {
        $pd = new \PharData($this->source) ;
        $pd->extractTo(BASE_TEMP_DIR."metadata.json", "metadata.json", true) ;
        $fData = file_get_contents(BASE_TEMP_DIR."metadata.json") ;
        $command = "del -y ".BASE_TEMP_DIR."metadata.json" ;
        self::executeAndOutput($command);
        $fdo = json_decode($fData) ;
        return $fdo ;
    }



}

function stream_notification_callback($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max) {
    static $filesize = null;

    switch($notification_code) {
        case STREAM_NOTIFY_RESOLVE:
        case STREAM_NOTIFY_AUTH_REQUIRED:
        case STREAM_NOTIFY_COMPLETED:
        case STREAM_NOTIFY_FAILURE:
        case STREAM_NOTIFY_AUTH_RESULT:
            /* Ignore */
            break;

        case STREAM_NOTIFY_REDIRECTED:
            echo "Being redirected to: ", $message, "\n";
            break;

        case STREAM_NOTIFY_CONNECT:
            echo "Connected...\n";
            break;

        case STREAM_NOTIFY_FILE_SIZE_IS:
            $filesize = $bytes_max;
            echo "Filesize: ", $filesize, "\n";
            break;

        case STREAM_NOTIFY_MIME_TYPE_IS:
            echo "Mime-type: ", $message, "\n";
            break;

        case STREAM_NOTIFY_PROGRESS:
            if ($bytes_transferred > 0) {
                if (!isset($filesize)) {
                    printf("\rUnknown filesize.. %2d kb done..", $bytes_transferred/1024);
                } else {
                    $length = (int)(($bytes_transferred/$filesize)*100);
                    printf("\r[%-100s] %d%% (%2d/%2d kb)", str_repeat("=", $length). ">", $length, ($bytes_transferred/1024), $filesize/1024);
                }
            }
            break;
    }
}