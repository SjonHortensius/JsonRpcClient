<?php namespace TooBasic\Rpc\Transport;
use TooBasic\Rpc;
use TooBasic\Exception;

class Curl implements Rpc\Transport
{
	protected $_options = [];
	protected $_clientUrl;
	protected $_client;

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

		if (isset($this->_client) && $url !== $this->_clientUrl)
		{
			curl_close($this->_client);
			unset($this->_client);
		}

		$this->_clientUrl = $url;
		if (!isset($this->_client))
			$this->_client = curl_init($url);

		// User-options first, so we can override them
		curl_setopt_array($this->_client, $this->_options);

		curl_setopt($this->_client, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($this->_client, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->_client, CURLOPT_HTTPHEADER, $headers);

		if (isset($body))
			curl_setopt($this->_client, CURLOPT_POSTFIELDS, $body);

		$response = curl_exec($this->_client);

		if (false === $response)
			throw new Exception('Error executing curl request: %s', [curl_error($this->_client)]);

		return $response;
	}

	public function __destruct()
	{
		if (isset($this->_client))
			curl_close($this->_client);
	}
}