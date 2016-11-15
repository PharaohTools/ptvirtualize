@home
Feature: Executing the program index page
  As a command line user
  I want to execute the index page
  To see overview information about the application

  Scenario: See Modules not hidden
    Given I run the application command in the shell
    Then I should see all of the modules which are not hidden

  Scenario: See Application Description
    Given I run the application command in the shell
    Then I should see the application description

  Scenario: See Pharaoh Tools Text
    Given I run the application command in the shell
    Then I should see the cli text "Pharaoh Tools"

  Scenario: See Pharaoh Exit Text
    Given I run the application command in the shell
    Then I should see the cli text "[Pharaoh Exit]"

  Scenario: Execute with "--only-compatible" parameter
    Given I run the application command in the shell with parameter string "--only-compatible"
    Then I should see only the modules which are compatible with this system

  Scenario: Execute with "--compatible-only" parameter
    Given I run the application command in the shell with parameter string "--compatible-only"
    Then I should see only the modules which are compatible with this system