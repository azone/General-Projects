<?php
function micro()
{
  list($s, $m) = explode(" ", microtime());
  return $s + $m;
}
$ts = micro();
$ip = new IPGeo($_GET['ip']);
print_r($ip->getInfo());

echo 'Use '.(micro() - $ts).' second';

class IPGeo
{
  const URL = 'http://api.hostip.info/?ip=';
  protected $ip;
  protected $ip_data = array();

  public function __construct($ip = null)
  {
    $this->ip = $ip;
    if($this->ip == null)
    {
      $this->ip = self::getIp();
    }

    $this->parseData();
  }

  public function getInfo()
  {
    return $this->ip_data;
  }

  public function parseData()
  {
    if(function_exists('curl_init'))
    {
      $this->curlParse();
    }
    else
    {
      $this->normalParse();
    }
  }

  public function curlParse()
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, self::URL.$this->ip);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    curl_close($ch);

    $this->parse($data);
  }

  public function normalParse()
  {
    $data = file_get_contents(self::URL.$this->ip);

    $this->parse($data);
  }

  public function parse($data)
  {
    $p = xml_parser_create();
    xml_parser_set_option($p, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($p, $data, $ret);
    xml_parser_free($p);

    $ip_data = array();
    $ip_data['ip'] = $_GET['ip'] ? $_GET['ip'] : $_SERVER['REMOTE_ADDR'];
    foreach($ret as $r)
    {
      if($r['tag'] == 'GML:NAME')
      {
        $ip_data['city'] = $r['value'];
      }

      if($r['tag'] == 'COUNTRYNAME')
      {
        $ip_data['country'] = $r['value'];
      }

      if($r['tag'] == 'COUNTRYABBREV')
      {
        $ip_data['country_code'] = $r['value'];
      }
    }

    $this->ip_data = $ip_data;
    unset($ip_data);
  }

  public static function getIp()
  {
    if(getenv('HTTP_X_FORWARDED_FOR'))
    {
      return getenv('HTTP_X_FORWARDED_FOR');
    }
    if(getenv('HTTP_CLIENT_IP'))
    {
      return getenv('HTTP_CLIENT_IP');
    }
    if(getenv('REMOTE_ADDR'))
    {
      return getenv('REMOTE_ADDR');
    }

    if($_SERVER['HTTP_X_FORWARDED_FOR'])
    {
      return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if($_SERVER('HTTP_CLIENT_IP'))
    {
      return $_SERVER('HTTP_CLIENT_IP');
    }
    if($_SERVER('REMOTE_ADDR'))
    {
      return $_SERVER('REMOTE_ADDR');
    }
  }
}
?>
