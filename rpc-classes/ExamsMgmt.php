<?php

class ExamsMgmt extends GenericMgmt
{

  const QUERY_GET_EXAMS = '
    SELECT * from exam WHERE start_date BETWEEN :start_date AND :end_date
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