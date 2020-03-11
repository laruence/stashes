<?php
const CURLE_OK = 0;
const CURLOPT_URL = 10002;
const CURLOPT_SSL_VERIFYHOST = 81;
const CURLOPT_SSL_VERIFYPEER = 64;
const CURLOPT_WRITEDATA = 10001;
const CURLOPT_WRITEFUNCTION = 20011;

$libcurl = get_libcurl();
$write  =  get_write();
$data = get_write_data($write);

$url = "https://www.laruence.com/2020/03/11/5475.html";


$ch = $libcurl->curl_easy_init();

$libcurl->curl_easy_setopt($ch, CURLOPT_URL, $url); 
$libcurl->curl_easy_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
$libcurl->curl_easy_setopt($ch, CURLOPT_WRITEDATA, get_data_addr($data));
$libcurl->curl_easy_setopt($ch, CURLOPT_WRITEFUNCTION, $write->init());
$libcurl->curl_easy_perform($ch);

$libcurl->curl_easy_cleanup($ch);

$ret = paser_libcurl_ret($data);
var_dump($ret);
