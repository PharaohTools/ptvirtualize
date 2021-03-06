<?php

Namespace Controller ;

class Status extends Base {

    public function execute($pageVars) {

        $thisModel = $this->getModelAndCheckDependencies(substr(get_class($this), 11), $pageVars) ;
        // if we don't have an object, its an array of errors
        if (is_array($thisModel)) { return $this->failDependencies($pageVars, $this->content, $thisModel) ; }

        $action = $pageVars["route"]["action"];

        if ($action=="help") {
            $helpModel = new \Model\Help();
            $this->content["helpData"] = $helpModel->getHelpData($pageVars["route"]["control"]);
            return array ("type"=>"view", "view"=>"help", "pageVars"=>$this->content); }

        $this->content["params"] = $thisModel->params ;

        if ($action=="show") {
            $this->content["result"] = $thisModel->statusShow();
            return array ("type"=>"view", "view"=>"status", "pageVars"=>$this->content); }

        if ($action=="full") {
            $this->content["result"] = $thisModel->statusFull();
            return array ("type"=>"view", "view"=>"status", "pageVars"=>$this->content); }

        if (in_array($action, array("vms", 'list-vms'))) {
            $this->content["result"] = $thisModel->listVms();
            return array ("type"=>"view", "view"=>"status", "pageVars"=>$this->content); }

        if (in_array($action, array('extended', "vm-execution"))) {
            $this->content["result"] = $thisModel->listVms(true);
            return array ("type"=>"view", "view"=>"status", "pageVars"=>$this->content); }

        if (in_array($action, array("fulldata"))) {
            $this->content["result"] = $thisModel->statusData();
            return array ("type"=>"view", "view"=>"status", "pageVars"=>$this->content); }

        $this->content["messages"][] = "Invalid Status Action";
        return array ("type"=>"control", "control"=>"index", "pageVars"=>$this->content);

    }

}