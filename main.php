<?php
/**
 *
 *	ZeroDream MCBBS 我的世界中文论坛自动签到工具
 *
 *	by Akkariin (Email: akkariins@gmail.com)
 *
 *	请务必保持本文件编码为 UTF-8，否则可能会乱码！
 *
 */
class ZeroDream {
	public function http($url, $post = '', $cookie = '', $returnCookie = 0) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
		curl_setopt($curl, CURLOPT_REFERER, $url);
		if ($post) {
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
		}
		if ($cookie) {
			curl_setopt($curl, CURLOPT_COOKIE, $cookie);
		}
		curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($curl);
		if (curl_errno($curl)) {
			return curl_error($curl);
		}
		curl_close($curl);
		if ($returnCookie) {
			list($header, $body) = explode("\r\n\r\n", $data, 2);
			preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
			$info['cookie'] = substr($matches[1][0], 1);
			$info['content'] = $body;
			return $info;
		} else {
			return $data;
		}
	}
	
	public function Println($str) {
		echo date("[Y-m-d H:i:s] ") . "{$str}\n";
	}
}
$ZeroDream = new ZeroDream();
date_default_timezone_set('Asia/Shanghai');   // 设置时区，防止时差错误
$text = urlencode("快乐咸鱼每一天~");         // 设置签到时填写的内容
if(!file_exists("cookie.txt")) {
	@file_put_contents("cookie.txt", "");
	$ZeroDream->Println("Please copy your cookie to cookie.txt!");
}
while(true) {
	if(intval(date("H")) == 4) {
		$cookie = @file_get_contents("cookie.txt");
		$data = curl_request("http://www.mcbbs.net/", false, $cookie);
		$data = mb_stristr($data, '<input type="hidden" name="formhash" value="');
		$formhash = mb_substr($data, 44, 8);
		$ZeroDream->Println("From Hash: {$formhash}");
		$post = Array(
			'formhash' => $formhash,
			'signsubmit' => 'yes',
			'handlekey' => 'signin',
			'emotid' => '1',
			'referer' => 'http://www.mcbbs.net/plugin.php?id=dc_signin',
			'content' => $text
		);
		curl_request("http://www.mcbbs.net/plugin.php?id=dc_signin:sign&inajax=1", $post, $cookie);
		$ZeroDream->Println("Sign successful!");
	} else {
		$ZeroDream->Println("Waiting for 4 a.m");
	}
	sleep(3600); // 一个小时后再判断一次
}
