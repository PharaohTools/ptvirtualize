<?php

Namespace Model;

// @todo i dont think we need a base class for this, we might, but i think were only wrapping about 10 commands
// @todo a base class might help wwith multi os though

class Shell extends BaseModelFactory {

    public static function getModel($params, $modGroup="Base") {
        $thisModule = substr(get_called_class(), 6) ;
        $model = \Model\SystemDetectionFactory::getCompatibleModel($thisModule, $modGroup, $params);
        return $model;
    }

}