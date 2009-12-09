<?php
class addMonth {
  private $timestamp, $old_month;

  /**
   * @param string or integer datetime string or Unix timestamp
   * @param integer month(s) to add
   */
  public function __construct($datetime, $month = 1)
  {
    $this->timestamp = $datetime;
    if(!preg_match('/^\d+$/', $datetime))
    {
      $this->timestamp = strtotime($datetime);
    }
    $this->old_month = $this->timestamp;
    $this->timestamp = strtotime('+'.(int)$month.' month', $this->old_month);
  }

  /**
   * @param string format, use date() function params
   * @return string formatted datetime | integer Unix Timestamp
   */
  public function getDay($format = null)
  {
    $prev_d = date('d', $this->old_month);
    $date = getdate($this->timestamp);
    $timestamp = $this->timestamp;
    if($date['mday'] < $prev_d)
    {
      $timestamp = mktime($date['hours'], $date['minutes'], $date['seconds'], $date['mon'] - 2, $prev_d - $date['mday'], $date['year']);
    }

    if(!is_null($format))
    {
      return date($format, $timestamp);
    }

    return $timestamp;
  }

  /**
   * @param string format, use date() function params
   * @return string formatted datetime | integer Unix Timestamp
   */
  public function getLastDay($format = null)
  {
    $date = getdate($this->getDay());

    $timestamp =  mktime($date['hours'], $date['minutes'], $date['seconds'], $date['mon'], date('t', $this->getDay()), $date['year']);

    if(!is_null($format))
    {
      return date($format, $timestamp);
    }

    return $timestamp;
  }
}
