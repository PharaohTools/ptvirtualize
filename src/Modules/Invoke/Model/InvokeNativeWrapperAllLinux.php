<?php

Namespace Model;

class InvokeNativeWrapperAllLinux extends Base {

    // Compatibility
    public $os = array("Linux") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("NativeWrapper") ;

    public $target ;
    public $privkey ;
    public $pubkey ;
    public $port ;
    public $timeout ;

    protected $connection ;

    public function login($username, $password = '') {
        if (file_exists($password)) {
            $this->privkey = $password  ;
            $connection = ssh2_connect($this->target, $this->port, array('hostkey'=>'ssh-rsa'));
            if ($this->pubkey == null) {
                $loggingFactory = new \Model\Logging();
                $logging = $loggingFactory->getModel($this->params) ;
                $logging->log("Native PHP SSH requires the public key to exist alongside the private. Using ".$this->privkey.".pub") ;
                $this->pubkey = $this->privkey.".pub" ; }
            if (ssh2_auth_pubkey_file($connection, $username, $this->pubkey, $this->privkey, 'secret')) {
                $this->connection = $connection ;
                return true ; } }
        else {
            $connection = ssh2_connect($this->target, $this->port);
            if (ssh2_auth_password($connection, $username, $password)) {
                $this->connection = $connection ;
                return true ; } }
        return false ;
    }

    public function exec($command) {
        var_dump($this->connection) ;
        $stream = ssh2_exec($this->connection, $command);

        var_dump($stream, stream_get_meta_data($stream)) ;
        stream_set_blocking( $stream, false );
        $md = stream_get_meta_data($stream);
        $all = stream_get_contents ($stream, -1, 0) ;
        while ($md["unread_bytes"] !== 0) {
            sleep(1) ;
            // var_dump($md["unread_bytes"], $md["eof"]) ;
            echo "\n...\n" ;
            $all .= stream_get_contents ($stream, -1, $md["unread_bytes"]) ;
            $md = stream_get_meta_data($stream);
        }
        //$all = stream_get_contents ($stream) ;
        fclose($stream);
        return $all ;
    }

}