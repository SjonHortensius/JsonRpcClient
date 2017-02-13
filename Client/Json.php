<?php namespace TooBasic\Rpc\Client;
use TooBasic\Rpc;
use TooBasic\Exception;

class Json
{
	protected $_url;
	protected $_transport;
	protected $_id = 1;
	protected $_isNotification = false;

	public function __construct($url, Rpc\Transport $transport)
	{
		$this->_url = $url;
		$this->_transport = $transport;
	}

	public function setNotification($n = true)
	{
		$this->_isNotification = (bool)$n;
	}

	public function __call($method, $params)
	{
		$requestId = $this->_isNotification ? null : $this->_id;
		$request = json_encode([
			'method' => $method,
			'params' => array_values($params),
			'id' => $requestId,
		]);

		$response = $this->_transport->request('POST', $this->_url, ['Content-type' => 'application/json'], $request);
		$response = json_decode($response);

		if ($this->_isNotification)
			return true;

		if ($response->id != $requestId)
			throw new Exception('Unexpected responseId '. $response->id .', expected '. $requestId);

		if (!is_null($response->error))
			throw new Exception('Request error: '. $response->error->message);

		return $response->result;
	}
}