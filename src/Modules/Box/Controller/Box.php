<?php

Namespace Controller ;

class Box extends Base {

    public function execute($pageVars) {

        $thisModel = $this->getModelAndCheckDependencies(substr(get_class($this), 11), $pageVars) ;
        // if we don't have an object, its an array of errors
        if (is_array($thisModel)) { return $this->failDependencies($pageVars, $this->content, $thisModel) ; }

        $action = $pageVars["route"]["action"];

        if ($action=="help") {
            $helpModel = new \Model\Help();
            $this->content["helpData"] = $helpModel->getHelpData($pageVars["route"]["control"]);
            return array ("type"=>"view", "view"=>"help", "pageVars"=>$this->content); }

        if ($action=="add") {
            $this->content["result"] = $thisModel->performBoxAdd();
            $this->content["appName"] = $thisModel->programNameInstaller ;
            return array ("type"=>"view", "view"=>"box", "pageVars"=>$this->content); }

        if ($action=="remove") {
            $this->content["result"] = $thisModel->performBoxRemove();
            $this->content["appName"] = $thisModel->programNameInstaller;
            return array ("type"=>"view", "view"=>"box", "pageVars"=>$this->content); }

        if ($action=="list") {
            $this->content["result"] = $thisModel->askInstall();
            $this->content["appName"] = $thisModel->programNameInstaller;
            return array ("type"=>"view", "view"=>"box", "pageVars"=>$this->content); }

        $this->content["messages"][] = "Invalid Box Action";
        return array ("type"=>"control", "control"=>"index", "pageVars"=>$this->content);

    }


}