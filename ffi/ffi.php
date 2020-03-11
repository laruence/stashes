<?php
const CURLE_OK = 0;
const CURLOPT_URL = 10002;
const CURLOPT_SSL_VERIFYHOST = 81;
const CURLOPT_SSL_VERIFYPEER = 64;
const CURLOPT_WRITEDATA = 10001;
const CURLOPT_WRITEFUNCTION = 20011;

$libcurl = FFI::scope("libcurl");
$write  = FFI::scope("write");

$url = "https://www.laruence.com/2020/03/11/5475.html";

$data = $write->new("own_write_data");

$ch = $libcurl->curl_easy_init();

$libcurl->curl_easy_setopt($ch, CURLOPT_URL, $url); 
$libcurl->curl_easy_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
$libcurl->curl_easy_setopt($ch, CURLOPT_WRITEDATA, FFI::addr($data));
$libcurl->curl_easy_setopt($ch, CURLOPT_WRITEFUNCTION, $write->init());
$libcurl->curl_easy_perform($ch);

$libcurl->curl_easy_cleanup($ch);

$ret = FFI::string($data->buf, $data->size);
