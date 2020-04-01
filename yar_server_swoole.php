<?php


class WhiteList {
	const HEADER_SIZE = 82;
	const YAR_MAGIC_NUM = 0x80DFEC60;

	protected $host;
	protected $dicts;
	protected $ids;
	protected $socket;

	public function __construct() {
		$options = getOpt("S:F:");
		if (!isset($options["S"]) || !isset($options["F"])) {
			$this->usage();
		}

		$this->host = $options["S"];
		$this->dicts = $options["F"];
	}

	protected function loadDict() {
		$this->ids = array();

	    $fp = fopen($this->dicts, "r");
		while (!feof($fp)) {
			$line = trim(fgets($fp));
			if ($line) {
				$this->ids[$line] = true;
			}
		}
		fclose($fp);
		echo "Loading dict successfully, ", count($this->ids), " loaded\n";

		return $this;
	}

	protected function usage() {
		exit("Usage: yar_server -F path_to_dict -S hostname:port\n");
	}

	protected function listen() {
        list($hostname, $port) = explode(":", $this->host);
        $server = new Swoole\Server($hostname, $port);
        echo "Starting Yar_Sever at {$this->host}\nPresss Ctrl + C to quit\n";

        $server->set([
            'open_length_check' => true, 
            'package_length_type' => 'N',
            'package_length_offset' => self::HEADER_SIZE - 4,
            'package_body_offset' => self::HEADER_SIZE,
        ]);
        
        $server->on("Receive", function ($_server, $fd, $tid, $data) {
            $buf = substr($data, 0, self::HEADER_SIZE);
            if (!$this->validHeader($header = $this->parseHeader($buf))) {
                $output = $this->response(1, "illegal Yar RPC request");
                goto response;
            }

            $buf = substr($data, self::HEADER_SIZE);
            if (!$this->validPackager($buf)) {
				$output = $this->response(1, "unsupported packager");
				goto response;
            }
            
            $buf = substr($data, self::HEADER_SIZE + 8); /* 跳过打包信息的8个字节 */
            $request = $this->parseRequest($buf);
            if ($request == false) {
                $output = $this->response(1, "malformed request body");
                goto response;
            }

            $status = $this->handle($request, $ret);
            $output = $this->response($status, $ret);
        
            response:
            $_server->send($fd, $output);
            $_server->close($fd);
        });

        $server->start();
	}


	protected function validHeader($header) {
		if ($header["magic_num"] != self::YAR_MAGIC_NUM) {
			return false;
		}
		return true;
	}

	protected function parseHeader($header) {
		return unpack("Nid/nversion/Nmagic_num/Nreserved/A32provider/A32token/Nbody_len", $header);
	}

	protected function genHeader($id, $len) {
		$bin = pack("NnNNA32A32N",
			$id, 0, 0x80DFEC60,
			0, "Yar PHP TCP Server",
			"", $len
		);
		return $bin;
	}

	protected function validPackager($buf) {
		if (strncmp($buf, "PHP", 3) == 0) {
			return true;
		}
		return false;
	}

	protected function parseRequest($body) {
		$request = unserialize($body);
		if (!isset($request["m"]) ||
			!isset($request["p"])) {
			return NULL;
		}
		return $request;
	}

    protected function query($id) {
		return isset($this->ids[$id]);
	}

	protected function handle($request, &$ret) {
		if ($request["m"] == "query") {
			$ret = $this->query(...$request["p"]);
		} else {
			$ret = "unsupported method '" . $request["m"]. "'";
			return 1;
		}
		return 0;
	}

	protected function response($status, $ret) {
		$body = array();

		$body["i"] = 0;
		$body["s"] = $status;
		if ($status == 0) {
			$body["r"] = $ret;
		} else {
			$body["e"] = $ret;
		}

		$packed = serialize($body);
		$header = $this->genHeader(0, strlen($packed) + 8);

		return $header . str_pad("PHP", 8, "\0") . $packed;
	}

	public function run() {
		$this->loadDict()->listen();
	}
}

(new WhiteList())->run();
