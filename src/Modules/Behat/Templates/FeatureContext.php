<?php

use Behat\Behat\Context\ContextInterface;
use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

use Behat\MinkExtension\Context\MinkContext;

/**
 * Behat context class.
 */
class FeatureContext extends MinkContext implements ContextInterface
{
    /**
     * Initializes context. Every scenario gets it's own context object.
     *
     * @param array $parameters Suite parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
    }

    /**
     * @Given /^I am on the home page$/
     */
    public function iAmOnTheHomePage()
    {
        $driver = new \Behat\Mink\Driver\Selenium2Driver('firefox');

        $session = new \Behat\Mink\Session($driver);

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
//        $driver = new \Behat\Mink\Driver\SeleniumDriver(
//            'firefox', 'base_url', $client
//        );
    }

}