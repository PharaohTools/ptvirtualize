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
//        error_reporting(0) ;
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
            if (is_dir($one_directory)) {
                $comm = 'cd '.$one_directory.' && dir /s/b Virtufile 2> NUL ' ;
                $res = self::executeAndLoad($comm) ;
                if ($res === '') { return false ; }
                $lines = explode("\n", $res) ;
//            var_dump($lines) ;
                $lines = $this->virtufileFilesOnly($lines) ;
//            var_dump('lines', $lines) ;
                $vms = $this->getVMFromLines($vms, $lines, $extended) ;
//            var_dump($vms) ;
            }
        }
        return $vms;
    }


    public function virtufileFilesOnly($lines) {
        $count = count($lines) ;
        $new_lines = [] ;
        for ($i=0; $i<$count; $i++) {
            if ($lines[$i] === '') {
                continue ;
            }
            $line_length = strlen($lines[$i]) ;
            $line_end = substr($lines[$i], $line_length-9) ;
            if ($line_end !== 'Virtufile') {
                continue ;
            }
            $new_lines[] = $lines[$i] ;
        }
        return $new_lines ;
    }

    public function loadStatusFromProvider($virtufile_path) {
        $virtufile_parent_path = dirname($virtufile_path) ;
        $comm = 'cd '.$virtufile_parent_path.' && ptvirtualize status fulldata --output-format=JSON 2> NUL' ;
        $json = shell_exec($comm) ;
        $array_data = json_decode($json) ;
        return $array_data ;
    }

}