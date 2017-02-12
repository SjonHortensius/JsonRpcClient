<?php namespace TooBasic\Rpc\Transport;
use TooBasic\Rpc;
use TooBasic\Exception;

class Socket implements Rpc\Transport
{
	protected $_host;
	protected $_port;
	protected $_socket;

	public function __construct($host, $port, Rpc\Transport $transport = null)
	{
		$this->_host = $host;
		$this->_port = $port;

		if (isset($transport))
			throw new Exception('Socket transport does not support chaining');
	}

	public function request(string $method, string $uri, array $headers = [], string $body = null): string
	{
		if (!empty($method) && !empty($uri))
		{
			$request = $method .' '. $uri .' HTTP/1.0'."\r\n";

			foreach ($headers as $k => $v)
				$request .= $k .': '. $v. "\r\n";

			$request .= "\r\n";
		}
		else
			$request = '';

		if (isset($body))
			$request .= $body;

		$this->_socket = stream_socket_client($this->_host .':'. $this->_port, $errno, $errstr);

		if (!$this->_socket)
			throw new Exception('Unable to connect to %s:%d; %s', [$this->_host, $this->_port, $errstr]);

		try
		{
			fwrite($this->_socket, $request);

			$response = '';
			while (!feof($this->_socket))
				$response .= fgets($this->_socket, 1024);
		}
		finally
		{
			fclose($this->_socket);
		}

		return $response;
	}
}