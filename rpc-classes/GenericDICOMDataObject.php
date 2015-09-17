<?php
class GenericDICOMDataObject extends GenericDataObject
{
  //@public
  public $dump;
  public $uid;
  private $_path;

  const UID_TAG = "0020,000d";
  const DCM_EXTENSION = 'dcm';


  public function __construct()
  {
    $this->update_dump();
    $this->update_uid();
  }

  /*
   * Meant to be overridden
   */
  public function update_dump()
  {
    throw new Exception('Need to specify an updateDump method for ' . get_class());
  }

  protected function update_uid()
  {
    $this->uid = $this->getTag(static::UID_TAG);
    if (strlen($this->uid) < 8 || !strstr($this->uid, '.'))
      throw new Exception('Cannot get a valid UID for this instance');
  }

  public function path()
  {
    if (!$this->has_dump_file())
      $this->_path =  DMWL_DCM_PATH . '/' . DMWL_AE_TITLE . '/' . $this->uid . '.dump';
    return $this->_path;
  }

  protected function setTag( $tag, $value ) {
    $this->dump = preg_replace( "/\%\[$tag\]/", $value, $this->dump );
  }

  /*
   * TODO: This is very basic.  Should actually check completeness
   */
  protected function is_complete() {
    return strstr($this->dump, ':' ) == 0;
  }

  protected function read_dump_from_path($path) {
    if ($path !== '' && file_exists($path)) {
      $this->dump = file_get_contents($path);
      $this->_path = $path;
    } else {
      $this->dump = static::DUMP_TEMPLATE;
      $this->_path = null;
    }
  }

  protected function get_date_time( $date_tag, $time_tag )
  {
    $date_value = $this->getTag($date_tag);
    $time_value = $this->getTag($time_tag);

    return DateTime::createFromFormat( 'YmdHis', $date_value. $time_value );
  }

  public function file_needs_update() {
    if ( !$this->has_dump_file() ) return true;
    if ( $this->dump != file_get_contents($this->path()) ) return true;
    return false;
  }

  public function update_files() {
    $this->delete_files();
    file_put_contents($this->path(), $this->dump);
    $cmd = escapeshellcmd(DCMTK_BIN_PATH . 'dump2dcm' );
    $arg1 = escapeshellarg( $this->path() );
    $arg2 = escapeshellarg( $this->path() . '.' . static::DCM_EXTENSION );
    exec( "$cmd --write-xfer-little $arg1 $arg2" );
  }

  public function delete_files() {
    $dump_path = $this->path();
    if (file_exists($dump_path))
      unlink($dump_path);

    $dcm_path = $dump_path . '.' . static::DCM_EXTENSION;
    if (file_exists($dcm_path))
      unlink($dcm_path);
  }

  protected function has_dump_file()
  {
    return ($this->_path && file_exists($this->_path));
  }

  protected function getDate( $date_tag )
  {
    $date_value = $this->getTag($date_tag);

    return DateTime::createFromFormat( 'Ymd', $date_value );
  }

  protected function getTime( $time_tag )
  {
    $time_value = $this->getTag($time_tag);

    return DateTime::createFromFormat( 'His', $time_value );
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