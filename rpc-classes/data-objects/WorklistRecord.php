<?php

class WorklistRecord extends GenericDataObject
{
  const DUMP_TEMPLATE = <<<EOD
(0008,0050) SH  :accession
(0008,0005) CS  [ISO_IR 100]
(0010,0010) PN  :patient_name
(0010,0020) LO  :patient_id
(0010,0030) DA  :patient_birthdate
(0010,0040) CS  :patient_gender
(0010,2000) LO  :medical_alerts
(0010,2110) LO  :contrast_allergies
(0020,000d) UI  :study_instance_uid
(0032,1032) PN  :requesting_physician
(0032,1060) LO  :requested_procedure_description
(0040,0100) SQ
(fffe,e000) -
(0008,0060) CS  :modality
(0040,0002) DA  :step_start_date
(0040,0003) TM  :step_start_time
(0040,0007) LO  :step_description
(0040,0010) SH  :step_station_name
(0040,0011) SH  :step_location
(0040,0400) LT
(fffe,e00d) -
(fffe,e0dd) -
(0040,1001) SH  :procedure_id
(0040,1003) SH  :priority
EOD;

  public $dump;

  public function __construct( $exam = null ) {
    $this->dump = self::DUMP_TEMPLATE;
  }

  public function setTag( $tag, $value ) {
    $this->dump = preg_replace( "/(\($tag\))(.+):.+/", "$1$2$3$value", $this->dump );
  }

  public function isComplete() {
    return strstr($this->dump, ':' ) == 0;
  }




}