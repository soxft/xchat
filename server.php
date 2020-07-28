<?php
set_time_limit(0);  //设置超时限制
define('MAX',20);   //限制最大缓存消息个数
define('SLEEP',50000);  //休息时间/用于缓解服务器压力 单位：微秒 //usleep(1000000)一秒
$type = $_GET['method'];
$time = $_GET['time'];
$input = $_GET['input'];
$name = $_GET['name'];
switch ($type) {
  case 'SEND':
    //前端发信息后端存入数据库
    $f = new file();
    $arr = $f -> read('database.json');
    if(empty($arr))
    {
      $arr = array();
    }
    // 获取原来的数据库
    $num = count($arr);
    if($num >= MAX)
    { 
      //设置最大保存信息个数,防止boom
      unset($arr[0]); //删除第一个(最老的)
      $arr[$num] = array(
        'time' => $time,
        'name' => $name,
        'input' => $input
      );
      $arr = array_values ($arr);//重新建立索引
    } else {
    $arr[$num] = array(
        'time' => $time,
        'name' => $name,
        'input' => $input
      );
    }
    $data = json_encode($arr);
    $f ->write('database.json',$data);
    echo '200';
    break;
  case 'GET':
    while(true){
      $f = new file();
      $arr = $f -> read('database.json');
      $j = 0;
      $NEW = array();
      if(empty($arr))
      {
        $arr = array();  //初始话
      }
      for($i = 0;$i <= count($arr) -1;$i++)
      {
        if((double)$arr[$i]['time'] >= (double)$time)
        {
          $NEW[$j] = $arr[$i];
          $timex = substr($arr[$i]['time'],0,-3);   //时间戳转换为时间
          $NEW[$j]['time'] = date("m/d H:i:s",$timex);
          $j++;
        }
      }
      // 获取原来的数据库遍历查询新消息
      if(!empty($NEW[0]))
      {
        echo json_encode($NEW);
        break;
      }
      usleep(SLEEP);
    }
    break;
}

class file {
  function write($filepath,$content) {
    file_put_contents($filepath, $content);
  }
  function read($filepath) {
    if (file_exists($filepath)) {
      $str = file_get_contents($filepath);
      return json_decode($str,true);
    } else {
      self::write($filepath,'');
    }
  }
}
?>