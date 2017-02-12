<?php namespace TooBasic\Rpc\Transport;
use TooBasic\Rpc;

class Scgi implements Rpc\Transport
{
	protected $_transport;

	public function __construct(Rpc\Transport $transport)
	{
		$this->_transport = $transport;
	}

	public function request(string $method, string $url, array $headers = [], string $body = null): string
	{
		$headers['Request-Method'] = $method;
		$headers['Request-Uri'] = $url;

		$headers = array_change_key_case($headers, CASE_UPPER);
		$headers = array_combine(
			str_replace('-', '_', array_keys($headers)),
			$headers
		);

		unset($headers['CONTENT_LENGTH'], $headers['SCGI']);

		$cgi = implode("\0", ['CONTENT_LENGTH', strlen($body), 'SCGI', 1]);
		foreach ($headers as $key => $value)
			$cgi .= $key."\0".$value."\0";

		$cgi = strlen($cgi).':'.$cgi.','.$body;

		return $this->_parseResponse($this->_transport->request("", "", [], $cgi));
	}

	protected function _parseResponse($response)
	{
		// Format of response is not covered by specifications; but attempt to remove headers
		if (preg_match('~^(Status: .+?)\r\n\r\n(.+)$~s', $response, $m))
		{
			$headers = [];
			foreach (explode("\r\n", $m[1]) as $line)
			{
				list($k, $v) = explode(': ', $line);
				$headers[ $k ] = $v;
			}

			$response = $m[2];
		}

		return $response;
	}
}