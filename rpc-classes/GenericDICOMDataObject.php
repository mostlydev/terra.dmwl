<?php
class GenericDICOMDataObject extends GenericDataObject
{
  //@public
  public $dump;
  public $uid;
  public $path;

  const UID_TAG = "0020,000d";

  public function __construct()
  {
    $this->updateDump();
    $this->updateUid();
  }

  public function updateDump()
  {
    throw new Exception('Need to specify an updateDump method for ' . self::get_class());
  }

  public function updateUid()
  {
    $this->uid = $this->getTag(self::UID_TAG);
  }

  public function setTag( $tag, $value ) {
    $this->dump = preg_replace( "/\%\[$tag\]/", $value, $this->dump );
  }

  public function isComplete() {
    return strstr($this->dump, ':' ) == 0;
  }

  protected function fromPath($path) {
    $this->path = $path;
    if ($path !== '' && file_exists($path)) {
      $this->dump = file_get_contents($path);
      $this->uid = $this->getTag( self::UID_TAG );
    } else {
      $this->dump = self::DUMP_TEMPLATE;
    }
  }

  protected function getDateTime( $dateTag, $timeTag )
  {
    $dateValue = $this->getTag($dateTag);
    $timeValue = $this->getTag($timeTag);

    return DateTime::createFromFormat( 'YmdHis', $dateValue. $timeValue );
  }

  protected function getDate( $dateTag )
  {
    $dateValue = $this->getTag($dateTag);

    return DateTime::createFromFormat( 'Ymd', $dateValue );
  }

  protected function getTime( $timeTag )
  {
    $timeValue = $this->getTag($timeTag);

    return DateTime::createFromFormat( 'His', $timeValue );
  }

  protected function getTag( $tag )
  {
    $value = preg_match( "/^(\($tag\))\s[A-Z]{2}([\s]+)(.+)$/m", $this->dump, $matches );
    return $value ? $matches[3] : null;
  }

  protected function cleanName($name)
  {
    $name = strtoupper($name);
    $name = preg_replace('/[\s]+/', '^', $name );
    return preg_replace( '/\./', '', $name);
  }

  /*
   * This is meant to be overridden by descendants but can be used as is to seed worklist dumps.
   */
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