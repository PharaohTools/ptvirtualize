<?php

class IndexTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).DIRECTORY_SEPARATOR."Bootstrap.php");
    }

    public function testExecuteReturnsAValue() {
        $controlObject = new \Controller\Index();
        $this->assertTrue ( $controlObject->execute( array() ) != null );
    }

    public function testExecuteReturnsAValueOfTypeArray() {
        $controlObject = new \Controller\Index();
        $this->assertTrue ( is_array($controlObject->execute( array() ) ) );
    }

}
