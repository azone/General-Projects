<?php
class  Pager
{
    private
      $url = '',
      $cur_page = 0,
      $next_page = 0,
      $prev_page = 0,
      $total_page = 0,
      $options = array();
    public function __construct($url, $total_page, $cur_page, $options = array())
    {
        $this->setUrl($url);
        $this->setTotalPage($total_page);
        $this->setCurPage($cur_page);

        $this->options['first_page'] = 1;
        $this->options['last_page'] = $this->getLastPage();
        $this->options['pager_start'] = '<div class="pager">';
        $this->options['pager_end'] = '</div>';
        $this->options['center_pages'] = 5;
        $this->options['param_name'] = 'page';
        $this->options['prev_page'] = '&lt;';
        $this->options['next_page'] = '&gt;';

        $this->setOptions($options);
    }

    public function createLinks()
    {
      extract($this->options, EXTR_SKIP);
      $ret_str = $pager_start;

      if($this->cur_page == 1)
      {
        $ret_str .= '<span class="current_page">'.$first_page.'</span>';
      }
      else
      {
        if($first_page == 1)
        {
          $ret_str .= $this->getLink($param_name.'='.$this->getPrevPage(), $prev_page);
          $ret_str .= $this->getLink($param_name.'=1', $first_page);
        }
        else
        {
          $ret_str .= $this->getLink($param_name.'=1', $first_page);
          $ret_str .= $this->getLink($param_name.'='.$this->getPrevPage(), $prev_page);
        }
      }

      $start = 1;
      if($this->cur_page >= $center_pages)
      {
        $ret_str .= '<span class="sep">...</span>';
        $start = $this->cur_page - floor($center_pages / 2);
      }

      for($i = $start; $i <= $start + $center_pages - 1 && $i < $this->total_page; $i++)
      {
        if($i == 1)
        {
          continue;
        }

        if($i == $this->cur_page)
        {
          $ret_str .= '<span class="current_page">'.$i.'</span>';
        }
        else
        {
          $ret_str .= $this->getLink($param_name.'='.$i, $i);
        }
      }

      if($this->cur_page + ($center_pages - floor($center_pages / 2)) < $this->total_page)
      {
        $ret_str .= '<span class="sep">...</span>';
      }

      if($this->cur_page < $this->total_page)
      {
        if($last_page == $this->total_page)
        {
          $ret_str .= $this->getLink($param_name.'='.$this->total_page, $last_page);
          $ret_str .= $this->getLink($param_name.'='.$this->getNextPage(), $next_page);
        }
        else
        {
          $ret_str .= $this->getLink($param_name.'='.$this->getNextPage(), $next_page);
          $ret_str .= $this->getLink($param_name.'='.$this->total_page, $last_page);
        }
      }
      else
      {
        $ret_str .= '<span class="current_page">'.$last_page.'</span>';
      }

      return $ret_str.$pager_end;
    }

    public function getLink($params, $name)
    {
      $link = '';
      if(is_array($params))
      {
        $amp = '';
        foreach($params as $key => $value)
        {
          $link .= $amp.$key.'='.$value;
          $amp = '&';
        }
      }
      else
      {
        $link .= $params;
      }

      if(strpos($this->url, '?') === false)
      {
         return '<a href="'.$this->url.'?'.$link.'">'.$name.'</a>';
      }

      return '<a href="'.$this->url.'&'.$link.'">'.$name.'</a>';
    }

    public function setUrl($url = '')
    {
      $this->url = rtrim($url, '?&');
    }

    public function setTotalPage($total_page = 0)
    {
      $this->total_page = $total_page;
    }

    public function setCurPage($cur_page = 0)
    {
      $this->cur_page = $cur_page;
    }

    public function setOptions($options = array())
    {
      $this->options = array_merge($this->options, $options);
    }

    public function setOption($key, $value)
    {
      $this->options[$key] = $value;
    }

    public function getPrevPage()
    {
      if($this->cur_page > 1)
      {
        return $this->cur_page - 1;
      }
      else
      {
        return 1;
      }
    }

    public function getNextPage()
    {
      if($this->cur_page < $this->total_page)
      {
        return $this->cur_page + 1;
      }
      else
      {
        return $this->total_page;
      }
    }

    public function getUrl()
    {
      return $this->url;
    }

    public function getTotalPage()
    {
      return $this->total_page;
    }

    public function getLastPage()
    {
      return $this->total_page;
    }

    public function getCurPage()
    {
      return $this->cur_page;
    }

    public function getOptions()
    {
      return $this->options;
    }

    public function getOption($key)
    {
      return isset($this->options[$key]) ? $this->options[$key] : null;
    }
}
