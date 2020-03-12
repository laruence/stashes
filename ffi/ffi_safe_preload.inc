<?php
class CURLOPT {
	const URL = 10002;
	const SSL_VERIFYHOST = 81;
	const SSL_VERIFYPEER = 64;
	const WRITEDATA = 10001;
	const WRITEFUNCTION = 20011;
}

FFI::load("curl.h");
FFI::load("write.h");

function get_libcurl() : FFI {
	return FFI::scope("libcurl");
}

function get_write_data($write) : FFI\CData {
	return $write->new("own_write_data");
}

function get_write() : FFI {
	return FFI::scope("write");
}

function get_data_addr($data) : FFI\CData {
	return FFI::addr($data);
}

function paser_libcurl_ret($data) :string{
	return FFI::string($data->buf, $data->size);
}
