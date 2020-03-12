<?php
$libcurl = get_libcurl();
$write  =  get_write();
$data = get_write_data($write);

$url = "https://www.laruence.com/2020/03/11/5475.html";


$ch = $libcurl->curl_easy_init();

$libcurl->curl_easy_setopt($ch, CURLOPT::URL, $url); 
$libcurl->curl_easy_setopt($ch, CURLOPT::SSL_VERIFYPEER, 0);
$libcurl->curl_easy_setopt($ch, CURLOPT::WRITEDATA, get_data_addr($data));
$libcurl->curl_easy_setopt($ch, CURLOPT::WRITEFUNCTION, $write->init());
$libcurl->curl_easy_perform($ch);

$libcurl->curl_easy_cleanup($ch);

$ret = paser_libcurl_ret($data);
var_dump($ret);
