<?php

Namespace Model;

class VirtualboxBoxAddLinuxMac extends BaseVirtualboxAllOS {

    // Compatibility
    public $os = array("Linux", "Darwin") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("BoxAdd") ;

    public function addBox($source, $target, $name) {
        // add the box here
        // create the directory for the box
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $boxDir = $this->createBoxDirectory($target, $name) ;
        if (!is_null($boxDir)) {
            // put the metadata file in the new box directory
            // find the name of the ova file in the tar
            // extract the ova file in the tar to the box directory
            // change the name of the ova file
            $this->extractMetadata($source, $boxDir) ;
            $ovaFile = $this->findOVA($source) ;
            $this->extractAll($source, $boxDir) ;
            $this->changeOVAName($boxDir, $ovaFile) ;
            $this->completion() ;
            return true; }
        return false ;
    }

    protected function askForBoxAddExecute() {
        if (isset($this->params["yes"]) && $this->params["yes"]==true) { return true ; }
        $question = 'Add Virtualbox Box Image?';
        return self::askYesOrNo($question);
    }

    protected function createBoxDirectory($target, $name) {
        $boxdir = $target . $name ;
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $command = "whoami" ;
        $whoami = self::executeAndLoad($command);
        $whoami = str_replace("\n", "", $whoami);
        $whoami = str_replace("\r", "", $whoami);
        if (file_exists($boxdir)) {
            $logging->log("Files already exist at $boxdir. Cannot create directory to add box.", $this->getModuleName());
            \Core\BootStrap::setExitCode(1);
            return null; }
        if (!file_exists($target)) {
            $logging->log("Adding parent box directory $target.", $this->getModuleName());
            $command = "sudo mkdir -p $boxdir" ;
            self::executeAndOutput($command);}
        $logging->log("Changing owner of parent box directory $target to $whoami.", $this->getModuleName());
        $command = "sudo chown $whoami $target" ;
        self::executeAndOutput($command);
        $logging->log("Changing user write permissions of parent box directory $target to 755.", $this->getModuleName());
        $command = "sudo chmod -R 755 $target" ;
        self::executeAndOutput($command);
        $logging->log("Adding box directory $boxdir.", $this->getModuleName());
        $command = "sudo mkdir -p $boxdir" ;
        self::executeAndOutput($command);
        $logging->log("Changing owner of box directory $boxdir to $whoami.", $this->getModuleName());
        $command = "sudo chown $whoami $boxdir" ;
        self::executeAndOutput($command);
        $logging->log("Changing user write permissions of box directory $boxdir to 755.", $this->getModuleName());
        $command = "sudo chown -R $whoami $boxdir" ;
        self::executeAndOutput($command);
        if (in_array($boxdir, array(false, null)) ) {
            $logging->log("Unable to create required Box directory", $this->getModuleName());
            return false ; }
        else {
            $logging->log("Box directory {$boxdir} created", $this->getModuleName());
            if (substr($boxdir, strlen($boxdir)-1, 1) !== DS) { $boxdir .= DS ; }
            return $boxdir ;}
    }

    protected function extractMetadata($source, $boxDir) {
            $boxFile = $source ;
            $command = "tar --extract --file=$boxFile -C ".$boxDir." ./metadata.json" ;
            self::executeAndOutput($command);
            if (file_exists($boxDir."metadata.json")) {
                $fData = file_get_contents($boxDir."metadata.json") ;
                $command = "rm ".$boxDir."metadata.json" ;
                self::executeAndOutput($command);
                $fdo = json_decode($fData) ;
                if (is_object($fdo)) { return $fdo ; } }
            else {
//            assume vagrant box
                // try if its a vagrant box
                $command = "tar --extract --file=$boxFile -C ".$boxDir." ."."Vagrantfile" ;
//            self::executeAndOutput($command);
//            if (file_exists($boxDir."Vagrantfile")) {
                $loggingFactory = new \Model\Logging();
                $logging = $loggingFactory->getModel($this->params) ;
//                $command = "rm ".$boxDir."Vagrantfile" ;
                self::executeAndOutput($command);
                $logging->log("Detected a Vagrant box, creating default vagrant box metadata", $this->getModuleName()) ;
                $metadata = new \StdClass();
                $metadata->provider = "virtualbox" ;
                $metadata->group = "none" ;
                $metadata->slug = "none" ;
                $metadata->home_location = "none" ;
                $fData = json_encode($metadata) ;
                $res = file_put_contents($boxDir."metadata.json", $fData) ;
                return $res ;}

            return false ;
        }

    protected function findOVA($source) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Finding ova file name from box file...", $this->getModuleName());
        $command = "tar -tvf $source" ;
        $allFilesString = self::executeAndLoad($command);
        $eachFile = explode("\n", $allFilesString) ;

        foreach ($eachFile as $oneFile) {
            $parts = preg_split('/\s+/', $oneFile);
            $last = max(array_keys($parts));
            $oneFile = $parts[$last] ;
            $fileExt = substr($oneFile, 3) ;
            if ($fileExt == ".ova" || $fileExt ==".ovf") {
                $rp = strrpos($oneFile, "./") ;
                if ($rp !== false) {
                    $oneFile = substr($oneFile, ($rp+2)) ; }
                $logging->log("Found ova file $oneFile from box file...", $this->getModuleName());
                return $oneFile ; } }
        return null ;
    }

    protected function extractAll($source, $boxDir) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Extracting all files from box file...", $this->getModuleName());
        $command = "tar --extract --file=$source -C $boxDir " ;
        self::executeAndOutput($command);
        $logging->log("Extraction complete...", $this->getModuleName());
    }

    protected function changeOVAName($boxDir, $ovaFile) {
        if ($ovaFile != "box.ova" && $ovaFile != "box.ovf") {
            $loggingFactory = new \Model\Logging();
            $logging = $loggingFactory->getModel($this->params) ;
            $logging->log("Changing ova file name from $ovaFile to box.ova...", $this->getModuleName());
            $command = "mv $boxDir/$ovaFile $boxDir/box.ova" ;
            self::executeAndOutput($command); }
    }

    protected function completion() {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params) ;
        $logging->log("Completed Adding Box...", $this->getModuleName());
    }

}