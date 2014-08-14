<?php

Namespace Info;

class AutoSSHInfo extends CleopatraBase {

  public $hidden = false;

  public $name = "AutoSSH - Use your Papyrus details to SSH into your Phlagrant box";

  public function __construct() {
    parent::__construct();
  }

  public function routesAvailable() {
    return array( "AutoSSH" =>   array("cli", "help") );
  }

  public function routeAliases() {
    return array("auto-ssh"=>"AutoSSH", "autossh"=>"AutoSSH", "ssh"=>"AutoSSH", "SSH"=>"AutoSSH");
  }

  public function helpDefinition() {
    $help = <<<"HELPDATA"
  This command allows you to autoSSH a phlagrant box

  AutoSSH, auto-ssh, autossh, ssh, SSH

        - cli
        Open an SSH Cli to your Phlagrant Box
        example: phlagrant auto-ssh cli

HELPDATA;
    return $help ;
  }

}