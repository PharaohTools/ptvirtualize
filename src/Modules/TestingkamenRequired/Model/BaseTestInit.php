<?php

Namespace Model;

class BaseTestInit extends Base {

  protected $installCommands;
  protected $uninstallCommands;
  protected $installUserName;
  protected $installUserHomeDir;
  protected $programExecutorCommand;

  public function __construct($params) {
    parent::__construct($params);
    $this->populateCompletion();
  }

  public function initialize() {
    $this->populateTitle();
  }

  public function askWhetherToInstallTestInit() {
    return $this->performTestInitInstall();
  }

  public function askInstall() {
    return $this->askWhetherToInstallTestInit();
  }

  public function askUnInstall() {
    return $this->askWhetherToUnInstallTestInit();
  }

  public function runAutoPilotInstall($autoPilot) {
    return $this->runAutoPilotTestInitInstall($autoPilot);
  }

  public function runAutoPilotUnInstall($autoPilot) {
    return $this->runAutoPilotTestInitUnInstall($autoPilot);
  }

  public function askWhetherToUnInstallTestInit() {
    return $this->performTestInitUnInstall();
  }

  private function performTestInitInstall() {
    $doInstall = (isset($this->params["yes"]) && $this->params["yes"]==true) ?
      true : $this->askWhetherToInstallTestInitToScreen();
    if (!$doInstall) { return false; }
    return $this->install();
  }

  private function performTestInitUnInstall() {
    $doUnInstall = (isset($this->params["yes"]) && $this->params["yes"]==true) ?
      true : $this->askWhetherToUnInstallTestInitToScreen();
    if (!$doUnInstall) { return false; }
    return $this->unInstall();
  }

  public function runAutoPilotTestInitInstall($autoPilot){
    $this->setAutoPilotVariables($autoPilot);
    $this->install($autoPilot);
    return true;
  }

  public function runAutoPilotTestInitUnInstall($autoPilot){
    $this->unInstall($autoPilot);
    return true;
  }

  public function install($autoPilot = null) {
    $this->showTitle();
    $this->executePreInstallFunctions($autoPilot);
    $this->doInitCommand();
    $this->executePostInstallFunctions($autoPilot);
    if ($this->programDataFolder) {
      $this->changePermissions($this->programDataFolder); }
    $this->extraCommands();
    // $this->setInstallFlagStatus(true) ;
    $this->showCompletion();
    return true;
  }

  public function unInstall($autoPilot = null) {
    $this->showTitle();
    $this->executePreUnInstallFunctions($autoPilot);
    $this->doUnInitCommand();
    $this->executePostUninstallFunctions($autoPilot);
    $this->extraCommands();
    // $this->setInstallFlagStatus(false) ;
    $this->showCompletion();
    return true;
  }

  private function showTitle() {
    print $this->titleData ;
  }

  private function showCompletion() {
    print $this->completionData ;
  }

  private function askWhetherToInstallTestInitToScreen(){
    $question = "Initialize ".$this->programNameInstaller."?";
    return self::askYesOrNo($question);
  }

  private function askWhetherToUnInstallTestInitToScreen(){
    $question = "Remove ".$this->programNameInstaller."?";
    return self::askYesOrNo($question);
  }

  protected function askForInstallUserName($autoPilot=null){
    if (isset($autoPilot) &&
      $autoPilot->{$this->autopilotDefiner."InstallUserName"} ) {
      $this->installUserName
        = $autoPilot->{$this->autopilotDefiner."InstallUserName"}; }
    else {
      $question = "Enter User To Install As:";
      $input = (isset($this->params["install-user-name"])) ? $this->params["install-user-name"] : self::askForInput($question);
      $this->installUserName = $input; }
  }

  protected function askForInstallUserHomeDir($autoPilot=null){
    if (isset($autoPilot) &&
      $autoPilot->{$this->autopilotDefiner."InstallUserHomeDir"} ) {
      $this->installUserHomeDir
        = $autoPilot->{$this->autopilotDefiner."InstallUserHomeDir"}; }
    else {
      $question = "Enter Install User Home Dir:";
      $input = (isset($this->params["install-user-home"])) ? $this->params["install-user-home"] : self::askForInput($question);
      $this->installUserHomeDir = $input; }
  }

  protected function askForInstallDirectory($autoPilot=null){
    if (isset($autoPilot) &&
      $autoPilot->{$this->autopilotDefiner."InstallDirectory"} ) {
      $this->programDataFolder
        = $autoPilot->{$this->autopilotDefiner."InstallDirectory"}; }
    else {
      $question = "Enter Install Directory:";
      $input = (isset($this->params["install-directory"])) ? $this->params["install-directory"] : self::askForInput($question);
      $this->programDataFolder = $input; }
  }

  private function doInitCommand(){
    self::swapCommandArrayPlaceHolders($this->installCommands);
    self::executeAsShell($this->installCommands);
  }

  private function changePermissions($autoPilot, $target=null){
    if ($target != null) {
      $command = "chmod -R 775 $target";
      self::executeAndOutput($command); }
  }

  protected function deleteExecutorIfExists(){
    $command = 'rm -f '.$this->programExecutorFolder.DIRECTORY_SEPARATOR.$this->programNameMachine;
    self::executeAndOutput($command, "Program Executor Deleted if existed");
    return true;
  }

  protected function saveExecutorFile(){
    $this->populateExecutorFile();
    $executorPath = $this->programExecutorFolder.DIRECTORY_SEPARATOR.$this->programNameMachine;
    file_put_contents($executorPath, $this->bootStrapData);
    $this->changePermissions(null, $executorPath);
  }

  private function populateExecutorFile() {
    $this->bootStrapData = "#!/usr/bin/php\n
<?php\n
exec('".$this->programExecutorCommand."');\n
?>";
  }

  private function doUnInitCommand(){
    self::swapCommandArrayPlaceHolders($this->uninstallCommands);
    self::executeAsShell($this->uninstallCommands);
  }

}