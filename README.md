Golden Contact Computing - Testingkamen
-------------------


Installation
-----------------

The preferred way to install any of the Pharoah apps (including this) is through cleopatra. If you install cleopatra
on your machine (http://github.com/phpengine/cleopatra), then you can install testingkamen using the following:

sudo cleopatra testingkamen install --yes=true

You can omit the --yes=true to pick your own installation directory. To install testingkamen cli on your machine
without cleopatra, do the following. You'll need to already have php5 and git installed.

git clone https://github.com/phpengine/cleopatra && sudo php cleopatra/install-silent

or...

git clone https://github.com/phpengine/cleopatra && sudo php cleopatra/install (If you want to choose the install dir)

... that's it, now the cleopatra command should be available at the command line for you.


About:
-----------------
Testingkamen is for Test Automation. It can be used to generate starter test suites for your applications,
and automated test execution scripts within minutes.

By providing an common API by which to execute tests in a wide range
of languages and test tools, you can run complex test suites across a range of platforms with little to no
extra configuration.



Usage:
-----------------

So, there are a few simple commands...

First, you can just use

testingkamen

...This will give you a list of the available modules...


Then you can use

testingkamen *ModuleName* help

...This will display the help for that module, and tell you a list of available alias for the module command, and the
available actions too.