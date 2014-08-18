<?php

Namespace Model;

class BaseShellAllOS extends Base {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default", "Base") ;

    protected $clientId ;
    protected $apiKey ;

    protected function askForShellAPIKey(){
        if (isset($this->params["digital-ocean-api-key"])) { return $this->params["digital-ocean-api-key"] ; }
        $papyrusVar = \Model\AppConfig::getProjectVariable("digital-ocean-api-key") ;
        if ($papyrusVar != null) {
            if (isset($this->params["guess"])) {
                return $papyrusVar ; }
            if (isset($this->params["use-project-api-key"]) && $this->params["use-project-api-key"] == true) {
                return $papyrusVar ; }
            $question = 'Use Project saved Shell API Key?';
            if (self::askYesOrNo($question, true) == true) { return $papyrusVar ; } }
        $appVar = \Model\AppConfig::getProjectVariable("digital-ocean-api-key") ;
        if ($appVar != null) {
            $question = 'Use Application saved Shell API Key?';
            if (self::askYesOrNo($question, true) == true) {
                return $appVar ; } }
        $question = 'Enter Shell API Key';
        return self::askForInput($question, true);
    }

    protected function askForShellClientID(){
        if (isset($this->params["digital-ocean-client-id"])) { return $this->params["digital-ocean-client-id"] ; }
        $papyrusVar = \Model\AppConfig::getProjectVariable("digital-ocean-client-id") ;
        if ($papyrusVar != null) {
            if ($this->params["guess"] == true) { return $papyrusVar ; }
            if ($this->params["use-project-client-id"] == true) { return $papyrusVar ; }
            $question = 'Use Project saved Shell Client ID?';
            if (self::askYesOrNo($question, true) == true) {
                return $papyrusVar ; } }
        $appVar = \Model\AppConfig::getProjectVariable("digital-ocean-client-id") ;
        if ($appVar != null) {
            $question = 'Use Application saved Shell Client ID?';
            if (self::askYesOrNo($question, true) == true) {
                return $appVar ; } }
        $question = 'Enter Shell Client ID';
        return self::askForInput($question, true);
    }

    protected function digitalOceanCall(Array $curlParams, $curlUrl){
        $curlParams["client_id"] = $this->clientId ;
        $curlParams["api_key"] = $this->apiKey;
        \Model\AppConfig::setProjectVariable("digital-ocean-client-id", $this->clientId) ;
        \Model\AppConfig::setProjectVariable("digital-ocean-api-key", $this->apiKey) ;
        $postQuery = "";
        $i = 0;
        foreach ($curlParams as $curlParamKey => $curlParamValue) {
            $postQuery .= ($i==0) ? "" : '&' ;
            if(is_object($curlParamValue)) {
                var_dump($curlParamKey, $curlParamValue) ;  }
            $postQuery .= $curlParamKey."=".$curlParamValue;
            $i++; }
        // echo $curlUrl.'?'.$postQuery ;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curlUrl.'?'.$postQuery);
        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec ($ch);
        curl_close ($ch);
        $callObject = json_decode($server_output);
        return $callObject;
    }


}