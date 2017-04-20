<?php

namespace Model ;

class InvokeBashSsh {

    // Compatibility
    public $os = array("any");
    public $linuxType = array("any");
    public $distros = array("any");
    public $versions = array("any");
    public $architectures = array("any");

    // Model Group
    public $modelGroup = array("DriverBashSSH");

	/**
	 * @var \Model\InvokeServer
	 */
	protected $server;

	/**
	 * @var string
	 */
	protected $connection;

	protected $commandsPipe;

	/**
	 * @param Server $server
	 */
	public function setServer($server) {
		$this->server = $server;
	}

	public function connect() {
        $launcher = $this->getLauncher() ;
        $this->commandsPipe = tempnam(null, 'ssh');
		$pipe = "tail -f {$this->commandsPipe}";
        if (!function_exists("pcntl_fork")) {
            $loggingFactory = new \Model\Logging();
            $logging = $loggingFactory->getModel($this->params);
            $logging->log("Unable to use pcntl_fork, ending", $this->getModuleName()) ;
            return false ; }
//        $pcntl = pcntl_fork() ;
//        var_dump("pcn", $pcntl) ;


//		if(!$pcntl){
//            $pcomm = "$pipe | $launcher" ;
            $pcomm = "$launcher 'echo Pharaoh Tools'" ;

            passthru($pcomm, $res) ;

//			$fp = popen("$pcomm" ,"r");
//            var_dump('fp', $fp) ;
//			while (!feof($fp)) {
//				echo fgets($fp, 4096);
//            @ flush(); }
//			pclose($fp);
//			exit;

//            }
//        else {

//            sleep(1) ;
//            $fp = popen("$pipe | $launcher" ,"r");
//            var_dump('fp', $fp) ;
//            while (!feof($fp)) {
//                echo fgets($fp, 4096); }
//            pclose($fp);
//            exit;
//        }
        return true ;
	}

	/**
	 * @param $command
	 * @return string
	 */
	public function exec($command) {
        $launcher = $this->getLauncher() ;
//        $pcomm = "$command | $launcher" ;
        $pcomm = "$launcher $command" ;
//        var_dump($pcomm) ;
        passthru($pcomm, $res) ;
//		file_put_contents($this->commandsPipe, $command.PHP_EOL, FILE_APPEND);
	}


	public function getLauncher() {
        if(file_exists($this->server->password)){
            $launcher = 'ssh -t -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -i '.escapeshellarg($this->server->password); }
        else{
            $launcher = 'sshpass -p '.escapeshellarg($this->server->password).' ssh -t -o UserKnownHostsFile=/dev/null ' .
                '-o StrictHostKeyChecking=no -o PubkeyAuthentication=no'; }
        $launcher .= " -T -p {$this->server->port} ";
        $launcher .= escapeshellarg($this->server->username.'@'.$this->server->host);
        return $launcher ;
    }

}