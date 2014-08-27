# Phlagrant, Pharaoh Tools

## About:

Virtual Machine and Development Environment management. Native PHP and complete integration with Pharaoh Tools for
provisioning. Automating, versioning, standardising and managing the setup, teardown and provisioning of virtualised
development environments for your projects.

Phlagrant is modular. object oriented and extendible, you can pretty easily write your own module if you want
functionality we haven't yet covered. Feel free to submit us pull requests.

This is part of the Pharaoh Tools suite, which covers Configuration Management, Test Automation Management, Automated
Deployment, Build and Release Management, Development Environment Management and more implemented using Infrastructure
as code in PHP.

Its easy to write modules for any Operating System but we've begun with Ubuntu and adding more as soon as possible.
Currently, all of the Modules work on Ubuntu 12, most on 13 and 14, and a few on Centos, Windows and Mac.

    
## Installation

The preferred way to install any of the Pharaoh apps (including this) is through cleopatra. If you install cleopatra
on your machine (http://git.pharaoh-tools.com/phpengine/cleopatra), then you can install phlagrant using the following:

sudo cleopatra phlagrant install --yes --guess

You can omit the --guess to pick your own installation directory. To install phlagrant cli on your machine
without cleopatra, do the following. You'll need to already have php5 and git installed.

To install phlagrant cli on your machine without cleopatra do the following:

sudo apt-get install php5 git

git clone https://git.pharaoh-tools.com/phpengine/phlagrant && sudo php phlagrant/install-silent

or...

git clone https://git.pharaoh-tools.com/phpengine/phlagrant && sudo php phlagrant/install
(if you want to choose the install location)

... that's it, now the phlagrant command should be available at the command line for you.


## Usage:

So, there are a few simple commands...

First, you can just use

phlagrant

...This will give you a list of the available modules...


Then you can use

phlagrant *ModuleName* help

...This will display the help for that module, and tell you a list of available alias for the module command, and the
available actions too.

You'll be able to automate any action from any available module into an autopilot file, or run it from the CLI. I'm
working on a web front end, but you can also use JSON output and the PostInput module to use any module from an API.


## A quick example

Fire up a virtual machine with a standard configuration of PHP.

 # create a directory, or use a current web project as your new Pharaoh project
 mkdir /var/www/my-test-project && cd /var/www/my-test-project

 # install virtualbox if you don't already have it (ideally with the guest additions iso)
 sudo cleopatra virtualbox install --yes --guess --with-guest-additions

 # add a default Cleopatra Configuration Management Autopilot file for
 sudo cleopatra cleofy install-generic-autopilots --yes --guess --template-group=phlagrant

 # flirtify
 phlagrant flirt now --template-group=default-php

 # install, configure and start the virtual machine
 phlagrant up now

That's it! you can sit back while Phlagrant creates your virtual machine environment for you. All of your system,
network and shared directory configuration for the Virtual Machine.


## Available Commands:

- Box - Box - Manage Base Boxes for Phlagrant
- Destroy - Destroy - Stop a Phlagrant Box
- EnvironmentConfig - Environment Configuration - Configure Environments for a project
- Flirtify - Phlagrant Flirtify - Generate a Phalgrantfile based on a template
- Halt - Halt - Stop a Phlagrant Box
- Logging - Logging - Output errors to the logging
- Resume - Resume - Stop a Phlagrant Box
- SystemDetection - System Detection - Detect the Running Operating System
- Templating - Templating
- Testify - Testifyer - Creates default tests for your project
- Up - Up - Create and Start a Phlagrant Box