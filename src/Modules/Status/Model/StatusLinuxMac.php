<?php

Namespace Model;

class StatusLinuxMac extends BaseFunctionModel {

    // Compatibility
    public $os = array("Linux", 'Darwin') ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default") ;

    public function __construct($params) {
        parent::__construct($params);
        $this->initialize();
    }

    public function statusShow() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $res = $this->loadFiles();
        if ($res === false) {
            $logging->log("Status module was unable to load a Virtufile", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
            return false ; }
        $this->findProvider("BoxStatus");
        return $this->provider->statusShow($this->virtufile->config["vm"]["name"]);
    }

    public function statusFull() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $res = $this->loadFiles();
        if ($res === false) {
            $logging->log("Status module was unable to load a Virtufile", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
            return false ; }
        $this->findProvider("BoxStatus");
        $result_object = array();
        $result_object['vm_name'] = $this->virtufile->config["vm"]["name"] ;
        $result_object['vm_status_output'] = $this->provider->statusFull($this->virtufile->config["vm"]["name"]);
        return $result_object ;
    }

    public function statusData() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $res = $this->loadFiles();
        if ($res === false) {
            $logging->log("Status module was unable to load a Virtufile", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
            return false ; }
        $this->findProvider("BoxStatus");
        $result_object = array();
        $result_object['vm_path'] = getcwd() ;
        $result_object['vm_name'] = $this->virtufile->config["vm"]["name"] ;
        $result_object['vm_status_output'] = $this->provider->statusData($this->virtufile->config["vm"]["name"]);
        $result_object['is-running'] = $this->provider->getRunStateFromData($result_object);
        $result_object['exists'] = $this->provider->existsInProvider($result_object);
        return $result_object ;
    }

    public function listVms($extended = false) {
        $timefile = '/tmp/vf'.time() ;
        $home = $_SERVER['HOME'] ;
        $default_directories = array($home, '/opt/') ;
        $vms = array() ;
        foreach ($default_directories as $default_directory) {
            $comm = 'find '.$default_directory.' -name Virtufile 2>&1 > '.$timefile ;
            ob_start() ;
            $res = self::executeAndGetReturnCode($comm) ;
            $empty = ob_get_clean();
            if ($res === false) {
                return false ;}
            $raw = file_get_contents($timefile) ;
            $lines = explode("\n", $raw) ;
            foreach ($lines as $line) {
                if (strpos($line, 'find: ') === 0) {
                   continue ;
                } else if (in_array($line, ['', ' '])) {
                   continue ;
                } else {
                    if ($extended === true) {
                        $one_vm = [];
                        $virtufile = $this->loadVirtufileAsRandomClass($line) ;
                        $one_vm['config'] = $virtufile->config ;
//                        var_dump($one_vm['config']) ;
//                        die() ;
                        $one_vm['provider'] = $this->loadStatusFromProvider($line) ;
                        $vms[$line] = $one_vm ;
                    } else {
                        $vms[] = $line ;
                    }
                }
            }
        }
        unlink($timefile) ;
        return $vms;
    }

    function loadVirtufileAsRandomClass($virtufile_path) {
        $string = file_get_contents($virtufile_path);
        $micro = microtime(true) ;
        $micro = str_replace('.', '', $micro) ;
        $random_class_name = 'TestVirtufile'.$micro ;
        $string_search = 'class Virtufile' ;
        $string_replace = 'class '.$random_class_name ;
        $string = str_replace($string_search, $string_replace, $string) ;
        $string_search = 'require' ;
        $string_replace = 'include' ;
        $string = str_replace($string_search, $string_replace, $string) ;
        $display_errors = ini_get('display_errors') ;
        $error_reporting = ini_get('error_reporting') ;
        set_error_handler(function(){});
        ini_set('display_errors', 'Off') ;
        ini_set('error_reporting', 0) ;
        ob_start() ;
        eval( '?>' . $string );
        ob_end_clean() ;
        ini_set('display_errors', $display_errors) ;
        error_reporting($error_reporting) ;
        $full_class = '\Model\\'.$random_class_name ;
        $virtufile_object = new $full_class() ;
        return $virtufile_object ;
    }

    function loadStatusFromProvider($virtufile_path) {
        $virtufile_parent_path = dirname($virtufile_path) ;
//        var_dump($virtufile_parent_path) ;
        $comm = 'cd '.$virtufile_parent_path.' && ptvirtualize status fulldata --output-format=JSON 2> /dev/null' ;
//        var_dump($comm) ;
        ob_start();
        $json = self::executeAndLoad($comm) ;
        ob_end_clean() ;
//        var_dump($json) ;
        $array_data = json_decode($json) ;
//        var_dump($array_data) ;
        return $array_data ;
    }

}