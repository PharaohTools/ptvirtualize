<?php

namespace Model ;

class PhpSecLib {

    // Compatibility
    public $os = array("any");
    public $linuxType = array("any");
    public $distros = array("any");
    public $versions = array("any");
    public $architectures = array("any");

    // Model Group
    public $modelGroup = array("DriverSecLib");

    /**
	 * @var Server
	 */
	protected $server;

	/**
	 * @var \Net_SSH2
	 */
	protected $connection;

    /**
     * @param Server $server
     */
    public function setServer($server)
    {
        $this->server = $server;
    }

	public function connect()
	{
		$this->connection = new \Net_SSH2($this->server->host, $this->server->port);
		if( ! $this->connection->login($this->server->username, $this->server->password) ){
			throw new \Exception("Login failed!");
		}
	}

	public function exec($command)
	{
		$this->connection->write("$command\n");
		$output = $this->connection->read('PHARAOHPROMPT');
		return str_replace("PHARAOHPROMPT", '', $output);
	}

	public function __call($k, $args = [])
	{
		return call_user_func_array([$this->connection, $k], $args);
	}

}