<?php

/*
 * Utility class for the terradb.  Used within TerraExam to retrieve records.
 */
class TerraExamsMgmt extends GenericMgmt
{

  const QUERY_GET_EXAMS = '
    SELECT exam.*, patient.P_bday as p_bday
    FROM exam
     INNER JOIN patient ON exam.ex_p_id = patient.P_id
     WHERE start_date BETWEEN :start_date AND :end_date
  ';
  public function __construct() {
    parent::__construct( );
  }

  public function getBetweenDates( $startDate, $endDate )
  {
    $stmt = $this->dbh->prepare( self::QUERY_GET_EXAMS );
    $stmt->bindValue( ':start_date', (string) $startDate->format('Y-m-d 00:00:00') );
    $stmt->bindValue( ':end_date', (string) $endDate->format('Y-m-d 23:59:59') );
    $stmt->execute();

    return $stmt->fetchAll();
  }
}