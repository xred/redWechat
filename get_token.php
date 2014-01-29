<?php 
require("wechat.class.php");
$wechat = new redWechat();
$wechat->save_token($appid,$secert);
 ?>
