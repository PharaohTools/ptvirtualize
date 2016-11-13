<?php

class IndexTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $bd = dirname(dirname(dirname(dirname(dirname(__FILE__))))).DIRECTORY_SEPARATOR ;
        try {
            require_once ($bd.'AutoLoad.php') ;
            $autoLoader = new \Core\autoLoader();
            $autoLoader->launch(); }
        catch (\Exception $e) {
            echo "Setup cant load autoloader\n" ;
            echo 'Message: ' .$e->getMessage(); }
        try {
            require_once ($bd.'Constants.php') ; }
        catch (\Exception $e) {
            echo "Setup cant load constants\n" ;
            echo 'Message: ' .$e->getMessage(); }
        try {
            require_once ($bd.'BootstrapCore.php') ; }
        catch (\Exception $e) {
            echo "Setup cant load Bootstrap Core Class\n" ;
            echo 'Message: ' .$e->getMessage(); }
    }

    public function testExecuteReturnsAValue() {
        $controlObject = new \Controller\Index();
        $res = $this->assertTrue ( $controlObject->execute( array() ) != null );
        return $res ;
    }

    public function testExecuteReturnsAValueOfTypeArray() {
        $controlObject = new \Controller\Index();
        $res = $this->assertTrue ( is_array($controlObject->execute( array() ) ) );
        return $res ;
    }

}
