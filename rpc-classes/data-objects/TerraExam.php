<?php

class TerraExam extends GenericDICOMDataObject
{
  const UID_TAG = "0020,000d";

  public $id;

  public $first_name;
  public $last_name;
  public $middle_name;
  public $patient_id;
  public $gender;
  public $allergy;
  public $birthdate;

  public $accession;
  public $modality;

  public $requesting_physician;
  public $requested_procedure_name;
  public $requested_procedure_id;
  public $requested_start_date;

  public function __construct( $row ) {
    $this->id= $row['exam_no'];
    $this->accession= $row['ex_acc_no'];
    $this->first_name= $row['ex_p_fname'];
    $this->last_name= $row['ex_p_lname'];
    $this->middle_name= $row['ex_p_mname'] !== 'NULL' ? $row['ex_p_mname'] : null ;
    $this->patient_id= $row['ex_p_id'];
    $this->requested_procedure_name= $row['ex_procname'];
    $this->requesting_physician= $row['ex_reqphy'];
    $this->gender= $row['ex_p_gender'];
    $this->modality= $row['ex_modality'];
    $this->requested_procedure_id= $row['ex_order_no'];
    $this->requested_start_date= new DateTime( $row['start_date'] );
    $this->allergy = $row['ex_allergy'];
    $this->birthdate = DateTime::createFromFormat('m-d-Y', $row['p_bday']);

    parent::__construct();
  }

  static function recent()
  {
    $mgr = new TerraExamsMgmt();
    $startDate = new DateTime();
    $startDate->sub( new DateInterval( 'P' . DMWL_MAX_AGE . 'D') );
    $endDate = new DateTime();

    return $mgr->rowsToDataObjects( 'TerraExam', $mgr->getBetweenDates($startDate, $endDate ) );
  }

  public function update_dump()
  {
    $this->dump = self::DUMP_TEMPLATE;
    $this->setTag("0008,0050", $this->accession);

    $this->setTag("0010,0010",
        $this->cleanName($this->last_name) . '^' .
        $this->cleanName($this->first_name) .
        ( $this->middle_name ? '^' . $this->cleanName($this->middle_name) : '' ) );

    $this->setTag("0040,0002", $this->requested_start_date->format('Ymd'));
    $this->setTag("0040,0003", $this->requested_start_date->format('His'));

    $this->uid = "1.2.826.0.1.3680043.2.1635.499192.{$this->id}";
    $this->setTag(self::UID_TAG, $this->uid );
    $this->setTag("0010,0020", $this->patient_id);
    $this->setTag("0010,0040", $this->gender);
    $this->setTag("0008,0060", $this->modality);
    $this->setTag("0032,1032", $this->requesting_physician );
    $this->setTag("0032,1060", $this->requested_procedure_name );
    $this->setTag("0010,2110", $this->allergy );
    $this->setTag("0010,0030", $this->birthdate->format('Ymd'));
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