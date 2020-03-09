![alt text](http://www.pharaohtools.com/images/logo-pharaoh.png "Pharaoh Tools Virtual Machine Management")

# PTVirtualize, Pharaoh Tools

## About:

Virtual Machine and Development Environment management. Native PHP and complete integration with Pharaoh Tools for
provisioning. Automating, versioning, standardising and managing the setup, teardown and provisioning of virtualised
development environments for your projects.

PTVirtualize is modular. object oriented and extendible, you can pretty easily write your own module if you want
functionality we haven't yet covered. Feel free to submit us pull requests.

This is part of the Pharaoh Tools suite, which covers Configuration Management, Test Automation Management, Automated
Deployment, Build and Release Management, Development Environment Management and more implemented using Infrastructure
as code in PHP.

Its easy to write modules for any Operating System but we've begun with Ubuntu and adding more as soon as possible.
Currently, PTVirtualize works smoothly Windows, OSx and Ubuntu.

    
## Installation

Our GUI Installers are almost ready

The preferred way to install any of the Pharaoh apps (including this) is through ptconfigure. If you install ptconfigure
on your machine (http://github.com/PharaohTools/ptconfigure), then you can install ptvirtualize using the following:

sudo ptconfigure ptvirtualize install --yes --guess

You can omit the --guess to pick your own installation directory. To install ptvirtualize cli on your machine
without ptconfigure, do the following. You'll need to already have php5 and git installed.

To install ptvirtualize cli on your machine without ptconfigure do the following:

sudo apt-get install php5 git -y

git clone http://github.com/PharaohTools/ptvirtualize && sudo php ptvirtualize/install-silent

or...

git clone http://github.com/PharaohTools/ptvirtualize && sudo php ptvirtualize/install
(if you want to choose the install location)

... that's it, now the ptvirtualize command should be available at the command line for you.


## Usage:

So, there are a few simple commands...

First, you can just use

ptvirtualize

...This will give you a list of the available modules...


Then you can use

ptvirtualize *ModuleName* help

...This will display the help for that module, and tell you a list of available alias for the module command, and the
available actions too.

You'll be able to automate any action from any available module into an autopilot file, or run it from the CLI. I'm
working on a web front end, but you can also use JSON output and the PostInput module to use any module from an API.


## A quick example

Fire up a virtual machine with a standard configuration of PHP.

 # create a directory, or use a current web project as your new Pharaoh project
 mkdir /var/www/my-test-project && cd /var/www/my-test-project

 # install virtualbox if you don't already have it (ideally with the guest additions iso)
 sudo ptconfigure virtualbox install --yes --guess --with-guest-additions

 # add a default PTConfigure Configuration Management Autopilot file for
 sudo ptconfigure cleofy install-generic-autopilots --yes --guess --template-group=ptvirtualize

 # init
 ptvirtualize init now --template-group=default-php

 # install, configure and start the virtual machine
 ptvirtualize up now

That's it! you can sit back while PTVirtualize creates your virtual machine environment for you. All of your system,
network and shared directory configuration for the Virtual Machine.


## Code Bits

# Composer install is required for Behat Tests

to execute behat tests...
composer install
behat --configuration build/tests/phpunit/phpunit.xml --log-junit=reports/junit/phpunit/output.xml  --coverage-clover reports/phpunit/xml/report.xml

to execute phpunit tests...
phpunit --configuration build/tests/phpunit/phpunit.xml --log-junit=reports/junit/phpunit/output.xml  --coverage-clover reports/phpunit/xml/report.xml
