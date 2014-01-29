<?php 
/**
* Wechat develop class by xred 2014.1.23
* Student@HUST
* PM@UniqueStudio 
* mailToMe:499126563@qq.com
* bitcoin address:19ECUpzBQLrocUGvR44LFr87ggqyfJgswN
*/
class redWechat
{
	public $post_Obj;
	public $time;
	public $fromusername;
	public $tousername;
	public $msgtype;
	public $event;
	public $event_key;
	public $keyword;
	public $location_x;
	public $location_y;
	public $picurl;
	public $msgid;
	public $mediaid;
	public $recognition;
	public $gh_token;
	public $user_state;
	public $precision;
	public $dirname = "./pre_userlist/";
	public $token_file = "token.save";
	public $log_file = "wechat.log";
	/**
	*初始化,会自动判断请求来源于公众平台的验证还是接收消息.
	* @param string $token 填写在公众平台后台的Token长度微3-32个字符
	*微信公众平台后台->高级功能->开发者模式获取
	*/
	function init($token)
	{
		$post_str =  $GLOBALS['HTTP_RAW_POST_DATA'];
		if ($post_str != "") {
			$this->response($post_str);
		}else{
			$this->valid($token);
		}
	}
	/**
	*获取access_token
	*@param string $appid
	*@param string $secret
	*微信公众平台后台->高级功能->开发者模式获取
	*/
	function save_token($appid,$secret)
	{
		$url = sprintf("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s",$appid,$secret);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$token_str = curl_exec($ch);
		$token_Obj = json_decode($token_str,true);
		$this->gh_token = $token_Obj['access_token'];
		file_put_contents($this->token_file, $this->gh_token);
	}
	/**
	*获取保存下来的token
	*/
	function get_token()
	{
		$this->gh_token = file_get_contents($this->token_file);
	}
	function valid($token)
	{
		$echoStr = $_GET['echostr'];
		if ($this->checkSignature($token)) {
			echo $echoStr;
			exit;
		}
	}
	function checkSignature($token)
	{
		$signature = $_GET['signature'];
		$timestamp = $_GET['timestamp'];
		$nonce = $_GET['nonce'];
		$tmpArray = array($token,$timestamp,$nonce);
		sort($tmpArray);
		$tmpStr = implode($tmpArray);
		$tmpStr = sha1($tmpStr);
		if ($tmpStr == $signature) {
			return true;
		}else{
			return false;
		}
	}
	/**
	*对微信服务器发来的数据进行解析
	*/
	function response($post_str)
	{
		file_put_contents($this->log_file, $post_str);
		$this->post_Obj = simplexml_load_string($post_str, 'SimpleXMLElement', LIBXML_NOCDATA);
		$this->tousername = $this->post_Obj->ToUserName;//the server id
		$this->fromusername = $this->post_Obj->FromUserName;
		if ($this->msgtype != "event") {
			$this->msgid = $this->post_Obj->MsgId;
		}
		$this->time = time();
		$this->msgtype = $this->post_Obj->MsgType;
		$this->user_state = $this->get_user_state();
		if ($this->msgtype == "text") {
			$this->keyword = $this->post_Obj->Content;
		}
		if ($this->msgtype == "location") {
			$this->location_x = $this->post_Obj->Location_X;
			$this->location_y = $this->post_Obj->Location_Y;
		}
		if ($this->msgtype == "event") {
			$this->event = $this->post_Obj->Event;
			if ($this->event == "CLICK") {
				$this->event_key = $this->post_Obj->EventKey;
			}
			if ($this->event == "LOCATION") {
				$this->location_x = $this->post_Obj->Latitude;
				$this->location_y = $this->post_Obj->Longitude;
				$this->precision = $this->post_Obj->Precision;
			}
			if ($this->event == "subscribe") {
				try {
					$this->event_key = $this->post_Obj->EventKey;
				} catch (Exception $e) {
					
				}
			}
			if ($this->event == "scan") {
				try {
					$this->event_key = $this->post_Obj->EventKey;
				} catch (Exception $e) {
					
				}
			}
		}
		if ($this->msgtype == "image") {
			$this->picurl = $this->post_Obj->PicUrl;
			$this->mediaid = $this->post_Obj->MediaId;
		}
		if ($this->msgtype =="voice") {
			$this->mediaid = $this->post_Obj->MediaId;
			try {
				$this->recognition = $this->post_Obj->Recognition;
			} catch (Exception $e) {
			}
		}
	}
	/**
	*回复文本信息
	*@param string $reply_text 
	*/
	function text_reply($reply_text)
	{
		$text_template = " <xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[text]]></MsgType>
		<Content><![CDATA[%s]]></Content>
		<FuncFlag>0</FuncFlag>
		</xml>";
		$result_str = sprintf($text_template,$this->fromusername,$this->tousername,$this->time,$reply_text);
		echo $result_str;
		exit;
	}
	/**
	*回复音乐信息
	*@param string $musicURL 音乐连接
	*@param string $HQMusciURL 在wifi情况下播放的音乐链接
	*@param string $title 标题
	*@param string $description 描述
	*/
	function music_reply($musicURL,$HQMusciURL="",$title,$description)
	{
		$music_template = " <xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[music]]></MsgType>
		<Music>
		<Title><![CDATA[%s]]></Title>
		<Description><![CDATA[%s]]></Description>
		<MusicUrl><![CDATA[%s]]></MusicUrl>
		<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
		</Music>
		<FuncFlag>0</FuncFlag>
		</xml>";
		$result_str = sprintf($music_template,$this->fromusername,$this->tousername,$this->time,$title,$description,$musicURL,$HQMusciURL);
		echo $result_str;
		exit;
	}
	/**
	*@param array $newslist
	*$newslist 支持两种情况的数组,并且会自行判断
	* 1.单条图文消息 eg:$newslist = array("title"=>"","description"=>"","url"=>"","picurl"=>"");
	* 2.多条图文消息 eg:$newslist = array($news1,$news2);其中$news1和$news2结构与1种相同
	*/
	function news_reply($newslist)
	{
		$newslist_length = count($newslist);
		$news_template = " <xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[news]]></MsgType>
		<ArticleCount>%s</ArticleCount>
		<Articles>
		%s
		</Articles>
		<FuncFlag>1</FuncFlag>
		</xml> ";
		$item_template = "<item>
		<Title><![CDATA[%s]]></Title>
		<Description><![CDATA[%s]]></Description>
		<PicUrl><![CDATA[%s]]></PicUrl>
		<Url><![CDATA[%s]]></Url>
		</item>";
		$item_list ="";
		$i = 0;
		foreach ($newslist as $value) {
			if (is_array($value)) {
				$child_is_array = 1;
			}else{
				$child_is_array = 0;
			}
			break;
		}
		if ($child_is_array) {
			foreach ($newslist as $newsItem) {
				if ($i==10) {
					break;
				}
				$i++;
				$item_list .= sprintf($item_template,$newsItem['title'],$newsItem['description'],$newsItem['picurl'],$newsItem['url']);
			}
		}else{
			$item_list .= sprintf($item_template,$newslist['title'],$newslist['description'],$newslist['picurl'],$newslist['url']);
			$newslist_length = 1;
		}
		$result_str = sprintf($news_template,$this->fromusername,$this->tousername,$this->time,$newslist_length,$item_list);
		echo $result_str;
		exit;
	}
	/**
	*@param string $openid 从$this->fromusername可以获取
	*get_user_state()以及set_user_state()采用文件的方式记录用户状态,故请确保有写入权限.另外建议改写成采用Redis改写.
	*/
	function get_user_state($openid)
	{
		if (file_exists($this->dirname)) {
			if (file_exists($this->dirname.$openid)) {
				$state = file_get_contents($this->dirname.$openid);
				return $state;
			}else{
				return "";
			}
		}else{
			return "";
		}
	}
	/**
	*@param string $openid 从$this->fromusername可以获取
	*@param string $state可以自定义一个状态
	*/
	function set_user_state($state,$openid)
	{
		if (file_exists($this->dirname)) {
			file_put_contents($this->dirname.$openid, $state);
		}else{
			mkdir($this->dirname);
			file_put_contents($this->dirname.$openid, $state);
		}
	}
	function post_json($url,$json)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',                                      
			'Content-Length: '.strlen($json))
		);
		$resultJson = curl_exec($ch);
		return $resultJson;
	}
	function get_json($url)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$str = curl_exec($ch);
		return $str;
	}
	/**
	*获取用户上传的文件的media_id需要高级权限支持(请先获取token)
	*/
	function get_media_link()
	{
		$url = sprintf("http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=%s&media_id=%s",$this->gh_token,$this->mediaid);
		return $url;
	}
	/**
	*上传media文件
	*图片（image）: 128K，支持JPG格式
	*语音（voice）：256K，播放长度不超过60s，支持AMR\MP3格式
	*视频（video）：1MB，支持MP4格式
	*缩略图（thumb）：64KB，支持JPG格式
	*媒体文件在后台保存时间为3天，即3天后media_id失效。
	*具体的格式\文件大小限制请参考:http://mp.weixin.qq.com/wiki/index.php?title=%E4%B8%8A%E4%BC%A0%E4%B8%8B%E8%BD%BD%E5%A4%9A%E5%AA%92%E4%BD%93%E6%96%87%E4%BB%B6
	*/
	function upload_media($file,$type)
	{
		$url = sprintf("http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=%s&type=%s",$this->gh_token,$type);
		$file = realpath($file);
		$fields['media'] = '@'.$file;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, true );
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result_str = curl_exec ($ch);
		curl_close ($ch);
		return $result_str;
		//return $result_str;
	}
	
	/*
	*获取用户基本信息返回值为json
	{
    "subscribe": 1, 
    "openid": "o6_bmjrPTlm6_2sgVt7hMZOPfL2M", 
    "nickname": "Band", 
    "sex": 1, 
    "language": "zh_CN", 
    "city": "广州", 
    "province": "广东", 
    "country": "中国", 
    "headimgurl":    "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/0", 
   "subscribe_time": 1382694957
	}
	参考http://mp.weixin.qq.com/wiki/index.php?title=%E8%8E%B7%E5%8F%96%E7%94%A8%E6%88%B7%E5%9F%BA%E6%9C%AC%E4%BF%A1%E6%81%AF
	*/
	function get_user_info()
	{
		$url = sprintf("https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s&lang=zh_CN",$this->gh_token,$this->fromusername);
		$user_info_str = file_get_contents($url);
		$user_info_obj = json_decode($user_info_str);
		return $user_info_obj;
	}
	/*
	获取关注者列表
	参考:http://mp.weixin.qq.com/wiki/index.php?title=%E8%8E%B7%E5%8F%96%E5%85%B3%E6%B3%A8%E8%80%85%E5%88%97%E8%A1%A8
	*/
	function get_follower_list($next_openid="")
	{
		if ($next_openid!="") {
			$url = sprintf("https://api.weixin.qq.com/cgi-bin/user/get?access_token=%s&next_openid=%s",$this->gh_token,$next_openid);
		}else{
			$url = sprintf("https://api.weixin.qq.com/cgi-bin/user/get?access_token=%s",$this->gh_token);
		}
		$follower_list_str = file_get_contents($url);
		$follower_list_obj = json_decode($follower_list_str);
		return $follower_list_obj;
	}
	/*
	important:由于开发时候使用的体验账号无法创建分组,故没有做分组相关的测试.这一块有bug请反馈给我,谢谢
	创建分组
	参考:http://mp.weixin.qq.com/wiki/index.php?title=%E5%88%86%E7%BB%84%E7%AE%A1%E7%90%86%E6%8E%A5%E5%8F%A3#.E5.88.9B.E5.BB.BA.E5.88.86.E7.BB.84
	*/
	function create_group($group_name)
	{
		$url =sprintf("https://api.weixin.qq.com/cgi-bin/groups/create?access_token=%s",$this->gh_token);
		$json = sprintf('{"group":{"name":"%s"}}',$group_name);
		$json = $this->$this->post_json($url,$json);
		return $json;
	}
	/*
	important:由于开发时候使用的体验账号无法创建分组,故没有做分组相关的测试.这一块有bug请反馈给我,谢谢
	查询所有分组
	参考:http://mp.weixin.qq.com/wiki/index.php?title=%E5%88%86%E7%BB%84%E7%AE%A1%E7%90%86%E6%8E%A5%E5%8F%A3
	*/
	function get_group_list()
	{
		$url = sprintf("https://api.weixin.qq.com/cgi-bin/groups/get?access_token=%s",$this->gh_token);
		$str = $this->get_json($url);
	}
	/*
	important:由于开发时候使用的体验账号无法创建分组,故没有做分组相关的测试.这一块有bug请反馈给我,谢谢
	查询用户所在分组
	参考:http://mp.weixin.qq.com/wiki/index.php?title=%E5%88%86%E7%BB%84%E7%AE%A1%E7%90%86%E6%8E%A5%E5%8F%A3
	*/
	function query_user_group($openid)
	{
		$url = sprintf("https://api.weixin.qq.com/cgi-bin/groups/getid?access_token=%s",$gh_token);
		$json = sprintf('{"openid":"%s"}',$openid);
		$json = $this->post_json($url,$json);
		return $json;
	}
	/*
	important:由于开发时候使用的体验账号无法创建分组,故没有做分组相关的测试.这一块有bug请反馈给我,谢谢
	修改分组名
	参考:http://mp.weixin.qq.com/wiki/index.php?title=%E5%88%86%E7%BB%84%E7%AE%A1%E7%90%86%E6%8E%A5%E5%8F%A3
	*/
	function change_group_name($groupid,$name)
	{
		$url = sprintf("https://api.weixin.qq.com/cgi-bin/groups/update?access_token=%s",$this->gh_token);
		$json = sprintf('{"group":{"id":%s,"name":"%s"}}',$groupid,$name);
		$json = $this->post_json($url,$json);
		return $json;
	}
	/*
	important:由于开发时候使用的体验账号无法创建分组,故没有做分组相关的测试.这一块有bug请反馈给我,谢谢
	移动用户分组
	参考:http://mp.weixin.qq.com/wiki/index.php?title=%E5%88%86%E7%BB%84%E7%AE%A1%E7%90%86%E6%8E%A5%E5%8F%A3
	*/
	function change_user_group($openid,$groupid)
	{
		$url = sprintf("https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token=%s",$this->gh_token);
		$json = sprintf('{"openid":"%s","to_groupid":%s}',$openid,$groupid);
		$json = $this->post_json($url,$json);
		return $json;
	}
	/*
	客服接口直接推送消息给用户
	发送文本消息,需要高级权限,请先获取access_token
	注意:需要用户在48小时之后与有过互动
	参考:http://mp.weixin.qq.com/wiki/index.php?title=%E5%8F%91%E9%80%81%E5%AE%A2%E6%9C%8D%E6%B6%88%E6%81%AF
	@param string $openid
	@param string $content_text
	*/
	function send_text_msg($openid,$content_text)
	{
		$url = sprintf("https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=%s",$this->gh_token);
		$msg = new stdClass();
		$content = new stdClass();
		$content->content = $content_text;
		$msg->touser = $openid;
		$msg->msgtype = "text";
		$msg->text = $content;
		$json = json_encode($msg);
		$json = $this->post_json($url,$json);
		return $json;
	}
	/*
	客服接口直接推送消息给用户
	发送图片消息,需要高级权限,请先获取access_token
	注意:需要用户在48小时之后与有过互动
	参考:http://mp.weixin.qq.com/wiki/index.php?title=%E5%8F%91%E9%80%81%E5%AE%A2%E6%9C%8D%E6%B6%88%E6%81%AF
	@param string $openid
	@param string $media_id
	media_id可以从用户发送的消息中获取,也可以自行上传,参考$this->upload_media()
	*/
	function send_img_msg($openid,$media_id)
	{
		$url = sprintf("https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=%s",$this->gh_token);
		$msg = new stdClass();
		$image = new stdClass();
		$image->media_id = $media_id;
		$msg->touser = $openid;
		$msg->msgtype = "image";
		$msg->image = $image;
		$json = json_encode($msg);
		$json = $this->post_json($url,$json);
		return $json;
	}
	/*
	客服接口直接推送消息给用户
	发送语音消息,需要高级权限,请先获取access_token
	注意:需要用户在48小时之后与有过互动
	参考:http://mp.weixin.qq.com/wiki/index.php?title=%E5%8F%91%E9%80%81%E5%AE%A2%E6%9C%8D%E6%B6%88%E6%81%AF
	@param string $openid
	@param string $media_id
	media_id可以从用户发送的消息中获取,也可以自行上传,参考$this->upload_media()
	*/
	function send_voice_msg($openid,$media_id)
	{
		$url = sprintf("https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=%s",$this->gh_token);
		$msg = new stdClass();
		$voice = new stdClass();
		$voice->media_id = $media_id;
		$msg->touser = $openid;
		$msg->msgtype = "voice";
		$msg->voice = $voice;
		$json = json_encode($msg);
		$json = $this->post_json($url,$json);
		return $json;
	}
	/*
	important 该接口为做测试,如果有bug请向我反馈
	客服接口直接推送消息给用户
	发送视频消息,需要高级权限,请先获取access_token
	注意:需要用户在48小时之后与有过互动
	参考:http://mp.weixin.qq.com/wiki/index.php?title=%E5%8F%91%E9%80%81%E5%AE%A2%E6%9C%8D%E6%B6%88%E6%81%AF
	@param string $openid
	@param string $media_id
	@param string $title
	@param string $description
	media_id可以从用户发送的消息中获取,也可以自行上传,参考$this->upload_media()
	*/
	function send_video_msg($openid,$media_id,$title,$description)
	{
		$url = sprintf("https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=%s",$this->gh_token);
		$msg = new stdClass();
		$video = new stdClass();
		$video->media_id = $media_id;
		$video->title = $title;
		$video->description = $description;
		$msg->touser = $openid;
		$msg->msgtype = "video";
		$msg->video = $video;
		$json = json_encode($msg);
		$json = $this->post_json($url,$json);
		return $json;
	}
	/*
	important 该接口为做测试,如果有bug请向我反馈
	客服接口直接推送消息给用户
	发送视频消息,需要高级权限,请先获取access_token
	注意:需要用户在48小时之后与有过互动
	参考:http://mp.weixin.qq.com/wiki/index.php?title=%E5%8F%91%E9%80%81%E5%AE%A2%E6%9C%8D%E6%B6%88%E6%81%AF
	@param string $openid
	@param string $title
	@param string $description
	@param string $musicurl 音乐链接
	@param string $hqmusicurl wifi下音乐链接
	@param string $thumb_media_id 缩略图media_id
	media_id可以从用户发送的消息中获取,也可以自行上传,参考$this->upload_media()
	*/
	function send_music_msg($openid,$title,$description,$musicurl,$hqmusicurl="",$thumb_media_id="")
	{
		$url = sprintf("https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=%s",$this->gh_token);
		$msg = new stdClass();
		$music = new stdClass();
		$music->title = $title;
		$music->description = $description;
		$music->musicurl = $musicurl;
		$music->hqmusicurl = $hqmusicurl;
		$music->thumb_media_id = $thumb_media_id;
		$msg->touser = $openid;
		$msg->msgtype = "music";
		$msg->music = $music;
		$json = json_encode($msg);
		$json = $this->post_json($url,$json);
		return $json;
	}
	/**
	*@param string $openid 用户的openid
	*@param array $article 新闻列表,结构参考:http://mp.weixin.qq.com/wiki/index.php?title=%E5%8F%91%E9%80%81%E5%AE%A2%E6%9C%8D%E6%B6%88%E6%81%AF#.E5.8F.91.E9.80.81.E5.9B.BE.E6.96.87.E6.B6.88.E6.81.AF
	*/
	function send_news_msg($openid,$articles)
	{
		$url = sprintf("https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=%s",$this->gh_token);
		$msg = new stdClass();
		$news = new stdClass();
		$news->articles = $articles;
		$msg->touser = $openid;
		$msg->msgtype = "news";
		$msg->news = $news;
		$json = json_encode($msg);
		$json = $this->post_json($url,$json);
		return $json;
	}
	/**
	*@param string $scene_id 二维码的参数
	*@param boolean $is_temp 是否为临时二维码
	*@param string $expire_seconds 如果为临时二维码,过期时间是多少(max=1800)
	*详情请参考: http://mp.weixin.qq.com/wiki/index.php?title=%E7%94%9F%E6%88%90%E5%B8%A6%E5%8F%82%E6%95%B0%E7%9A%84%E4%BA%8C%E7%BB%B4%E7%A0%81
	*/
	function create_qrcode($scene_id,$is_temp=false,$expire_seconds="1800")
	{
		if ($expire_seconds>1800) {
			$expire_seconds = 1800;
		}
		$url = sprintf("https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=%s",$this->gh_token);
		if ($is_temp == true) {
			$json = sprintf('{"expire_seconds": %s, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": %s}}}',$expire_seconds,$scene_id);
		}else{
			$json = sprintf('{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": %s}}}',$scene_id);
		}
		$json = $this->post_json($url,$json);
		$obj = json_decode($json);
		$ticket = $obj->ticket;
		$qr_url = sprintf("https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=%s",$ticket);
		return $qr_url;
	}

}
?>