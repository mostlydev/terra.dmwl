<?php

class Exam extends GenericDataObject
{
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
  }
}