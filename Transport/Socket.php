<?php namespace TooBasic\Rpc\Transport;
use TooBasic\Rpc;
use TooBasic\Exception;

class Socket implements Rpc\Transport
{
	protected $_enableRaw = false;

	public function __construct(Rpc\Transport $nextTransport = null)
	{
		if (isset($nextTransport))
			throw new Exception(__CLASS__. ' does not support chaining');
	}

	public function request(string $method, string $url, array $headers = [], string $body = null): string
	{
		$url = (object)parse_url($url);

		if (empty($method) && empty($headers))
			$request = $body;
		else
		{
			$request = $method .' '. $url->path.$url->query .' HTTP/1.0'."\r\n";

			foreach ($headers as $k => $v)
				$request .= $k .': '. $v. "\r\n";

			$request .= "\r\n";

			if (isset($body))
				$request .= $body;
		}

		$socket = stream_socket_client($url->host .':'. $url->port, $errno, $errstr);

		if (!$socket)
			throw new Exception('Unable to connect to %s; %s', [$url->host, $errstr]);

		try
		{
			fwrite($socket, $request);

			$response = '';
			while (!feof($socket))
				$response .= fgets($socket, 1024);
		}
		finally
		{
			fclose($socket);
		}

		return $response;
	}
}