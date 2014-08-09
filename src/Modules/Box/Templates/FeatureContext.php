<?php

use Box\Box\Context\ContextInterface;
use Box\Box\Exception\PendingException;
use Box\Gherkin\Node\PyStringNode;
use Box\Gherkin\Node\TableNode;

use Box\MinkExtension\Context\MinkContext;

/**
 * Box context class.
 */
class FeatureContext extends MinkContext implements ContextInterface
{
    /**
     * Initializes context. Every scenario gets it's own context object.
     *
     * @param array $parameters Suite parameters (set them up through box.yml)
     */
    public function __construct(array $parameters)
    {
    }

    /**
     * @Given /^I am on the home page$/
     */
    public function iAmOnTheHomePage()
    {
        $driver = new \Box\Mink\Driver\Selenium2Driver('firefox');

        $session = new \Box\Mink\Session($driver);

        // start session:
        $session->start();

        // open some page in browser:
        $session->visit('<%tpl.php%>site_url</%tpl.php%>');

        // get the current page URL:
        echo $session->getCurrentUrl();

        // stop session:
        $session->stop();

    }

    /**
     * @Then /^I should see some text$/
     */
    public function iShouldSeeSomeText()
    {
//        $client = new \Selenium\Client($host, $port);
//        $driver = new \Box\Mink\Driver\SeleniumDriver(
//            'firefox', 'base_url', $client
//        );
    }

}