<?php

Namespace Controller ;

class Initialise extends Base {

    public function execute($pageVars) {

        $thisModel = $this->getModelAndCheckDependencies(substr(get_class($this), 11), $pageVars) ;
        // if we don't have an object, its an array of errors
        if (is_array($thisModel)) { return $this->failDependencies($pageVars, $this->content, $thisModel) ; }

        $action = $pageVars["route"]["action"];

        if ($action=="help") {
            $helpModel = new \Model\Help();
            $this->content["helpData"] = $helpModel->getHelpData($pageVars["route"]["control"]);
            return array ("type"=>"view", "view"=>"help", "pageVars"=>$this->content); }

        $now_options = array('now', 'file', 'virtufile') ;
        if (in_array($action, $now_options)) {
            $thisModel = $this->getModelAndCheckDependencies(substr(get_class($this), 11), $pageVars, 'Default') ;
            $this->content["result"] = $thisModel->askWhetherToInitialise();
            if (is_array($thisModel)) { return $this->failDependencies($pageVars, $this->content, $thisModel) ; }
            return array ("type"=>"view", "view"=>"initialise", "pageVars"=>$this->content); }

        $this->content["messages"][] = "Invalid Initialise Action";
        return array ("type"=>"control", "control"=>"index", "pageVars"=>$this->content);

    }

}