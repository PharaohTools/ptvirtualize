Logging log
  log-message "Lets install our Application to test"

User ensure-exists
  when "{{{ PTWebApplication::~::isNotOSX }}}"
  label "Ensure {{{ Parameter::app-slug }}} user exists"
  username "{{{ Parameter::app-slug }}}"
  fullname "{{{ Parameter::app-slug }}}"
  home-directory ""
  shell "/bin/bash"

# mkdir -p reports/junit/behat ;
# mkdir -p reports/html/behat ;

Mkdir path
  label "Ensure the JUnit Output Directory exists"
  path "{{{ Facts::Runtime::factGetConstant::PFILESDIR }}}/{{{ Parameter::app-slug }}}/{{{ Parameter::app-slug }}}/reports/junit/behat"

Mkdir path
  label "Ensure the HTML Output Directory exists"
  path "{{{ Facts::Runtime::factGetConstant::PFILESDIR }}}/{{{ Parameter::app-slug }}}/{{{ Parameter::app-slug }}}/reports/html/behat"

Logging log
  log-message "About to generate behat config yaml"

RunCommand install
  guess
  command "php ptvirtualize/build/tests/behat/yaml-generator.php"

Logging log
  log-message "About to run Behat tests"

Logging log
  log-message "Behat Tests must run from repo root, to use composer json for autoloading"


RunCommand install
guess
command "cd $$test_app_source_root && behat --config $$test_app_source_root/build/tests/behat/behat_gen.yml --suite=core_features -f junit -o $$test_app_source_root/reports/junit/behat -f progress -o std "

#

# echo "Now convert the junit test output to html";
# /usr/local/bin/junit-viewer --results=`pwd`/../reports/junit/behat --save=`pwd`/../reports/html/behat/index.html;

Chmod path
  label "Ensure the Repositories Directory is writable"
  path "{{{ Facts::Runtime::factGetConstant::REPODIR }}}"
  recursive true
  mode '0755'

PHPModules ensure
  label "Ensure PHP Default Modules are installed"

Logging log
  log-message "Our application installation is complete"
