<?php

class ProcessHelper extends GenericHelper
{
  private $descriptorspec;
  public $cmd;
  public $args;
  public $cwd;

  public function __construct( $cmd, $args, $cwd ) {
    $basename = basename( $cmd );
    $this->cmd = $cmd;
    $this->args = $args;
    $this->cwd = $cwd;

    $this->descriptorspec = array(
        2 => array("file", LOGS_PATH . "$basename.log", "a") // stderr is a file to write to
    );

    $this->assert_cwd_exists();
  }

  public function run()
  {
    $process = proc_open( $this->cmd . " " . $this->args, $this->descriptorspec, $pipes, $this->cwd );
    if (is_resource($process)) {
      $return_value = proc_close($process);

      return $return_value;
    } else {
      return -1;
    }
  }

  private function assert_cwd_exists() {
    if (!file_exists($this->cwd))
      throw new Exception('cannot-find-process-working-path');
  }
}