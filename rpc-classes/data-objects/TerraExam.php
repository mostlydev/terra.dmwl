<?php

class TerraExam extends GenericDICOMDataObject
{
  const UID_TAG = "0020,000d";

  public $no;
  public $acc_no;
  public $p_fname;
  public $p_lname;
  public $p_mname;
  public $p_id;
  public $procname;
  public $reqphy;
  public $p_gender;
  public $modality;
  public $order_no;
  public $start_date;
  public $end_date;
  public $allergy;
  public $p_bday;

  public function __construct( $row ) {
    $this->no= $row['exam_no'];
    $this->acc_no= $row['ex_acc_no'];
    $this->p_fname= $row['ex_p_fname'];
    $this->p_lname= $row['ex_p_lname'];
    $this->p_mname= $row['ex_p_mname'] !== 'NULL' ? $row['ex_p_mname'] : null ;
    $this->p_id= $row['ex_p_id'];
    $this->procname= $row['ex_procname'];
    $this->reqphy= $row['ex_reqphy'];
    $this->p_gender= $row['ex_p_gender'];
    $this->p_modality= $row['ex_modality'];
    $this->order_no= $row['ex_order_no'];
    $this->start_date= new DateTime( $row['start_date'] );
    $this->end_date= new DateTime( $row['end_date'] );
    $this->allergy = $row['ex_allergy'];
    $this->p_bday = DateTime::createFromFormat('m-d-Y', $row['p_bday']);

    parent::__construct();
  }

  public function updateDump()
  {
    $this->dump = self::DUMP_TEMPLATE;
    $this->setTag("0008,0050", $this->acc_no);

    $this->setTag("0010,0010",
        $this->cleanName($this->p_lname) . '^' .
        $this->cleanName($this->p_fname) .
        ( $this->p_mname ? '^' . $this->cleanName($this->p_mname) : '' ) );

    $this->setTag("0040,0002", $this->start_date->format('Ymd'));
    $this->setTag("0040,0003", $this->start_date->format('His'));

    $this->uid = "1.2.826.0.1.3680043.2.1635.499192.{$this->no}";
    $this->setTag(self::UID_TAG, $this->uid );
    $this->setTag("0010,0020", $this->p_id);
    $this->setTag("0010,0040", $this->p_gender);
    $this->setTag("0008,0060", $this->modality);
    $this->setTag("0032,1032", $this->reqphy );
    $this->setTag("0032,1060", $this->procname );
    $this->setTag("0010,2110", $this->allergy );
    $this->setTag("0010,0030", $this->p_bday->format('Ymd'));
  }

  static function recent()
  {
    $mgr = new TerraExamsMgmt();
    $startDate = new DateTime();
    $startDate->sub( new DateInterval( 'P' . DMWL_MAX_AGE . 'D') );
    $endDate = new DateTime();

    return $mgr->rowsToDataObjects( 'TerraExam', $mgr->getBetweenDates($startDate, $endDate ) );
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