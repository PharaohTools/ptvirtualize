<?php

Namespace Model ;

class Phlagrantfile extends PhlagrantfileBase {

    public $config ;

    public function __construct() {
        $this->setConfig();
    }

    private function setConfig() {
        $this->setDefaultConfig();
        $this->config["vm"]["ostype"] = "Ubuntu_64" ;
        $this->config["vm"]["name"] = "daves_box" ;
        $this->config["vm"]["box"] = "VanillaUbuntu_14.04" ;
        $this->config["vm"]["memory"] = "1024" ;
        $this->config["vm"]["cpus"] = 1 ;
    }

}
