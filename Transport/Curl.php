<?php namespace TooBasic\Rpc\Transport;
use TooBasic\Rpc;
use TooBasic\Exception;

class Curl implements Rpc\Transport
{
	protected $_options = [];

	public function __construct(Rpc\Transport $nextTransport = null)
	{
		if (isset($nextTransport))
			throw new Exception(__CLASS__. ' does not support chaining');
	}

	public function setOption(int $key, $value)
	{
		$this->_options[ $key ] = $value;
	}

	public function request(string $method, string $url, array $headers = [], string $body = null): string
	{
		array_walk($headers, function(&$v, $k){
			$v = $k .': '. $v;
		});

		$c = curl_init($url);

		try
		{
			// User-options first, so we can override them
			curl_setopt_array($c, $this->_options);

			curl_setopt($c, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($c, CURLOPT_HTTPHEADER, $headers);

			if (isset($body))
				curl_setopt($c, CURLOPT_POSTFIELDS, $body);

			$response = curl_exec($c);

			if (false === $response)
				throw new Exception('Error executing curl request: %s', [curl_error($c)]);

			return $response;
		}
		finally
		{
			curl_close($c);
		}
	}
}