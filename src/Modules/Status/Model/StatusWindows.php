<?php

Namespace Model;

class StatusWindows extends StatusLinuxMac {

    // Compatibility
    public $os = array("Windows", 'WINNT') ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default") ;

    public function listVms($extended = false) {
        error_reporting(0) ;
        if (isset($this->params['search'])) {
            $directories = json_decode($this->params['search']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $directories = explode(',', $this->params['search']) ;
            }
        } else {
            $home = getenv('HOME') ;
            $directories = array($home) ;
        }
        $vms = array() ;
        foreach ($directories as $one_directory) {
            $comm = 'cd '.$one_directory.' && dir /s/b Virtufile ' ;
            $res = self::executeAndLoad($comm) ;
            if ($res === '') { return false ; }
            $lines = explode("\n", $res) ;
            $vms = $this->getVMFromLines($vms, $lines, $extended) ;
        }
        return $vms;
    }


    public function loadStatusFromProvider($virtufile_path) {
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