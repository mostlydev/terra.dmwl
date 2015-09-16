<?php

class WorklistRecord extends GenericDataObject
{
  public $dump;
  public $path;
  public $studyInstanceUid;

  public function __construct( $source = null )
  {
    parent::__construct();

    if (is_string($source) == 'String' )
    {
      $this->fromPath($source);
    }
    elseif (get_class($source) == 'Exam' )
    {
      $this->fromExam( $source );
    } else {
      throw new Exception('Worklist record must have a valid source');
    }
  }

  public function needsUpdate() {
    if (!file_exists($this->path)) return true;
    if ($this->dump !== file_get_contents($this->path)) return true;
    return false;
  }

  public function update() {
    file_put_contents($this->path, $this->dump);
    $cmd = escapeshellcmd(DCMTK_BIN_PATH . 'dump2dcm' );
    $arg1 = escapeshellarg( $this->path );
    $arg2 = escapeshellarg( $this->path . '.dcm' );
    exec( "$cmd --write-xfer-little $arg1 $arg2" );
  }

  public function isStale() {
    $cutoff = new DateTime();
    $cutoff = $cutoff->sub( new DateInterval('P'. DMWL_MAX_AGE .'D') );
    return ( $this->getDate("0040,0002") < $cutoff );
  }

  public function setTag( $tag, $value ) {
    $this->dump = preg_replace( "/\%\[$tag\]/", $value, $this->dump );
  }

  public function isComplete() {
    return strstr($this->dump, ':' ) == 0;
  }

  private function fromPath($path) {
    $this->path = $path;
    if ($path) {
      $this->dump = file_get_contents($path);
      $this->studyInstanceUid = $this->getTag("0020,000d" );
    } else {
      $this->dump = self::DUMP_TEMPLATE;
    }
  }

  public function getDateTime( $dateTag, $timeTag )
  {
    $dateValue = $this->getTag($dateTag);
    $timeValue = $this->getTag($timeTag);

    return DateTime::createFromFormat( 'YmdHis', $dateValue. $timeValue );
  }

  public function getDate( $dateTag )
  {
    $dateValue = $this->getTag($dateTag);

    return DateTime::createFromFormat( 'Ymd', $dateValue );
  }

  public function getTime( $timeTag )
  {
    $timeValue = $this->getTag($timeTag);

    return DateTime::createFromFormat( 'His', $timeValue );
  }

  public function getTag( $tag )
  {
    $value = preg_match( "/^(\($tag\))\s[A-Z]{2}([\s]+)(.+)$/m", $this->dump, $matches );
    return $value ? $matches[3] : null;
  }

  private function fromExam($exam)
  {
    $this->dump = self::DUMP_TEMPLATE;
    $this->setTag("0008,0050", $exam->acc_no);

    $this->setTag("0010,0010",
        $this->cleanName($exam->p_lname) . '^' .
        $this->cleanName($exam->p_fname) .
        ( $exam->p_mname ? '^' . $this->cleanName($exam->p_mname) : '' ) );

    $this->setTag("0040,0002", $exam->start_date->format('Ymd'));
    $this->setTag("0040,0003", $exam->start_date->format('His'));

    $this->studyInstanceUid = "1.2.826.0.1.3680043.2.1635.499192.{$exam->no}";
    $this->setTag("0020,000d", $this->studyInstanceUid );
    $this->setTag("0010,0020", $exam->p_id);
    $this->setTag("0010,0040", $exam->p_gender);
    $this->setTag("0008,0060", $exam->modality);
    $this->setTag("0032,1032", $exam->reqphy );
    $this->setTag("0032,1060", $exam->procname );
    $this->setTag("0010,2110", $exam->allergy );
    $this->setTag("0010,0030", $exam->p_bday->format('Ymd'));

    $this->path = DMWL_DCM_PATH . '/' . DMWL_AE_TITLE . '/' . $this->studyInstanceUid . '.dump';
  }

  private function cleanName($name)
  {
    $name = strtoupper($name);
    $name = preg_replace('/[\s]+/', '^', $name );
    return preg_replace( '/\./', '', $name);
  }

  const DUMP_TEMPLATE = <<<EOD
(0008,0050) SH  %[0008,0050]
(0008,0005) CS  [ISO_IR 100]
(0010,0010) PN  %[0010,0010]
(0010,0020) LO  %[0010,0020]
(0010,0030) DA  %[0010,0030]
(0010,0040) CS  %[0010,0040]
(0010,2110) LO  %[0010,2110]
(0020,000d) UI  %[0020,000d]
(0032,1032) PN  %[0032,1032]
(0032,1060) LO  %[0032,1060]
(0040,0100) SQ
(fffe,e000) -
(0008,0060) CS  %[0008,0060]
(0040,0002) DA  %[0040,0002]
(0040,0003) TM  %[0040,0003]
(0040,0400) LT
(fffe,e00d) -
(fffe,e0dd) -
EOD;

}