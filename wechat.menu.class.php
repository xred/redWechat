<?php 
/**
* wechat menu class by red 2014.1.24
*/
class wechatMenu
{
	var $token;
	function init($appid,$secret)
	{
		$url = sprintf("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s",$appid,$secret);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$token_str = curl_exec($ch);
		$token_Obj = json_decode($token_str,true);
		$this->token = $token_Obj['access_token'];
	}
	function set_menu($menu)
	{
		$post_url = sprintf("https://api.weixin.qq.com/cgi-bin/menu/create?access_token=%s",$this->token);
		$ch = curl_init($post_url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $menu);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',                                      
			'Content-Length: '.strlen($menu))
		);
		$resultJson = curl_exec($ch);
		$resultObj = json_decode($resultJson,true);
		echo $resultObj['errmsg'];
	}
	function query_menu()
	{
		$url = sprintf("https://api.weixin.qq.com/cgi-bin/menu/get?access_token=%s",$this->token);
		$ch =curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$menu_str = curl_exec($ch);
		echo $menu_str;
	}
	function delete_menu()
	{
		$url = sprintf("https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=%s",$this->token);
		$ch =curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$delete_str = curl_exec($ch);
		$delete_Obj = json_decode($delete_str);
		echo $delete_Obj['errmsg'];
	}
}
?>