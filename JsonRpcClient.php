<?php
class JsonRpcClient
{
	private $_url;
	private $_id = 1;
	private $_isNotification = false;

	public function __construct($url)
	{
		$this->_url = $url;
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

		$c = curl_init($this->_url);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_HTTPHEADER, ['Content-type' => 'application/json']);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_POSTFIELDS, $request);

		$response = curl_exec($c);
		$response = json_decode($response);

		curl_close($c);

		if ($this->_isNotification)
			return true;

		if ($response->id != $requestId)
			throw new Exception('Unexpected responseId '. $response->id .', expected '. $requestId);

		if (!is_null($response->error))
			throw new Exception('Request error: '. $response->error->message);

		return $response->result;
	}
}