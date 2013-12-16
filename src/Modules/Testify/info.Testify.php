<?php

Namespace Info;

class TestifyInfo extends Base {

    public $hidden = false;

    public $name = "Testifyer - Creates default tests for your project";

    public function __construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "Testify" =>  array_merge(parent::routesAvailable(), array("standard-php", "joomla", "drupal7",
          "php-js", "html-js") ) );
    }

    public function routeAliases() {
      return array("testify"=>"Testify");
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  Testify allows you to create and configure default tests for projects.

  Testify, testify

        - standard-php
        Create a default set of tests in build/tests for a standard PHP project. PHPUnit Tests,
        Jasmine for Javascript, Behat/Mink and Ruby/Cucumber
        example: cleopatra testify standard-php

        - joomla
        Create a default set of tests in build/tests for a Joomla PHP project. PHPUnit Tests,
        Jasmine for Javascript, Behat/Mink and Ruby/Cucumber
        example: cleopatra testify joomla

        - drupal7
        Create a default set of tests in build/tests for a Drupal 7 project. Simpletest,
        Behat/Mink and Ruby/Cucumber
        example: cleopatra testify drupal7

        - php-js
        Create a default set of tests in build/tests for a standard PHP/JS project.
        example: cleopatra testify php-js

        - html-js
        Create a default set of tests in build/tests for a standard HTML/JS project.
        example: cleopatra testify html-js

HELPDATA;
      return $help ;
    }

}