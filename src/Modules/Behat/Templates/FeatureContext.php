<?php

use Behat\Behat\Context\ContextInterface;
use Behat\Behat\Snippet\Context\SnippetsFriendlyInterface;
use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Behat context class.
 */
class FeatureContext extends MinkContext implements ContextInterface, SnippetsFriendlyInterface
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
     * @Given /^I am on the home page "([^"]*)"$/
     */
    public function iAmOnTheHomePage($argument1)
    {
        $client = new \Selenium\Client($host, $port);
        $driver = new \Behat\Mink\Driver\SeleniumDriver(
            'firefox', 'base_url', $client
        );
        $session = new \Behat\Mink\Session($driver);

        // start session:
        $session->start();
        // open some page in browser:
        $session->visit('http://my_project.dev/some_page.php');

        // get the current page URL:
        echo $session->getCurrentUrl();

        // get the response status code:
        echo $session->getStatusCode();
    }

    /**
     * @Then /^I should see some text "([^"]*)"$/
     */
    public function iShouldSeeSomeText($argument1)
    {
        $client = new \Selenium\Client($host, $port);
        $driver = new \Behat\Mink\Driver\SeleniumDriver(
            'firefox', 'base_url', $client
        );
    }



}
