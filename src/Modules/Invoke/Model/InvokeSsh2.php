<?php

namespace Model;

class InvokeSsh2 extends BaseLinuxApp {

    // Compatibility
    public $os = array("any");
    public $linuxType = array("any");
    public $distros = array("any");
    public $versions = array("any");
    public $architectures = array("any");

    // Model Group
    public $modelGroup = array("DriverNativeSSH");

    /**
     * @var
     */
    private $connection;

    /**
     * @var
     */
    private $stream;

    /**
	 * @var Server
	 */
	private $server;

    /**
     * @param Server $server
     */
    public function setServer($server)
    {
        $this->server = $server;
    }

	/**
	 * @throws \Exception
	 */
    public function connect() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (!function_exists("ssh2_connect")) {
            $logging->log('Native PHP SSH2 Functions are not installed.', "Invoke - PHP SSH") ;
            $logging->log('Trying to install Native PHP SSH2 Functions.', "Invoke - PHP SSH") ;
            $comm = SUDOPREFIX.PTCCOMM.'phpssh install -yg' ;
            $rc = $this->executeAndGetReturnCode($comm, true, true);
            if ($rc["rc"] == 0) {
                $logging->log('PHP Native SSH Driver Installed', "Invoke - PHP SSH") ;
                $sys = new \Model\SystemDetectionAllOS();
                if ($sys->os=="Darwin") {
                    $logging->log('As an OSX User your installation will require you to re-run this command, to use the newly installed Native SSH PHP Extension', "Invoke - PHP SSH") ;
                    \Core\BootStrap::setExitCode(1) ;
                    return false ; } }
            else {
                $logging->log('Cannot use the PHP Native SSH Driver.', "Invoke - PHP SSH") ;
                \Core\BootStrap::setExitCode(1) ;
                return false ; } }
        if (!($this->connection = ssh2_connect($this->server->host, $this->server->port))) {
            $logging->log('Cannot connect to server', "Invoke - PHP SSH") ;
            \Core\BootStrap::setExitCode(1) ;
            return false; }
        $rt = ssh2_auth_password($this->connection, $this->server->username, $this->server->password);
        return $rt ;
    }

	/**
	 * @param $command
	 * @return string
	 * @throws \Exception
	 */
	public function exec($command) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        if (!function_exists("ssh2_connect")) {
            $logging->log('Native PHP SSH2 Functions are not installed.', "Invoke - PHP SSH") ;
            $logging->log('Trying to install Native PHP SSH2 Functions.', "Invoke - PHP SSH") ;
            $comm = PTCCOMM.' phpssh install -yg' ;
            $rc = $this->executeAndGetReturnCode($comm);
            if ($rc["rc"] != 0) {
                $logging->log('Cannot use the PHP Native SSH Driver.', "Invoke - PHP SSH") ;
                \Core\BootStrap::setExitCode(1) ;
                return false; }
            else {
                $logging->log('PHP Native SSH Driver Loaded', "Invoke - PHP SSH") ;  } }
		if (!($this->stream = ssh2_exec($this->connection, $command, "vanilla"))) {
            $loggingFactory = new \Model\Logging();
            $logging = $loggingFactory->getModel($this->params) ;
            $logging->log("SSH command failed", "Invoke - PHP SSH") ;
            \Core\BootStrap::setExitCode(1) ; }
		stream_set_blocking($this->stream, true);
		$data = "";
		while ($buf = fread($this->stream, 4096)) {
			$data .= $buf;
            echo $buf ; }
		fclose($this->stream);
		return "";
	}

	/**
	 * @throws \Exception
	 */
	public function disconnect() {
		$this->exec('echo "EXITING" && exit;');
		$this->connection = null;
	}
}