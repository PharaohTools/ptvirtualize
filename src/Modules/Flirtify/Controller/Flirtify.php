<?php

Namespace Controller ;

class Flirtify extends Base {

    public function execute($pageVars) {

        $thisModel = $this->getModelAndCheckDependencies(substr(get_class($this), 11), $pageVars) ;
        // if we don't have an object, its an array of errors
        if (is_array($thisModel)) { return $this->failDependencies($pageVars, $this->content, $thisModel) ; }

        $action = $pageVars["route"]["action"];

        if ($action=="help") {
            $helpModel = new \Model\Help();
            $this->content["helpData"] = $helpModel->getHelpData($pageVars["route"]["control"]);
            return array ("type"=>"view", "view"=>"help", "pageVars"=>$this->content); }

        if ($action=="now") {
            $this->content["result"] = $thisModel->askWhetherToFlirtify();
            return array ("type"=>"view", "view"=>"flirtify", "pageVars"=>$this->content); }

        $actionsToModelGroups = array(
            "medium" => "Medium", "medium-web" => "MediumWeb", "db-cluster" => "DBCluster", "tiny" => "Tiny",
            "workstation" => "Workstation" ) ;

        if (in_array($action, array_keys($actionsToModelGroups))) {
            $thisModel = $this->getModelAndCheckDependencies(substr(get_class($this), 11), $pageVars, $actionsToModelGroups[$action]) ;
            $this->content["result"] = $thisModel->askWhetherToFlirtify();
            if (is_array($thisModel)) { return $this->failDependencies($pageVars, $this->content, $thisModel) ; }
            return array ("type"=>"view", "view"=>"flirtify", "pageVars"=>$this->content); }

        $this->content["messages"][] = "Invalid Flirtify Action";
        return array ("type"=>"control", "control"=>"index", "pageVars"=>$this->content);

    }

}