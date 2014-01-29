<?php 
require("wechat.class.php");
$wechat = new redWechat();
$wechat->init("wechat");
if ($wechat->msgtype == "text") {
	$wechat->text_reply("hello world");
}
if ($wechat->event == "subscribe") {
	$wechat->text_reply("welcome !");
}
 ?>