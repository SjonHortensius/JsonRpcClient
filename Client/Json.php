<?php namespace TooBasic\Rpc\Client;
use TooBasic\Rpc;
use TooBasic\Exception;

class Json
{
	protected $_url;
	protected $_transport;
	protected static $_id = 1;
	protected $_isNotification = false;
	protected $_enableDebug = false;

	public function __construct(string $url, Rpc\Transport $transport)
	{
		$this->_url = $url;
		$this->_transport = $transport;
	}

	public function setNotification(bool $n = true): void
	{
		$this->_isNotification = $n;
	}

	public function setDebug(bool $d = true): void
	{
		$this->_enableDebug = $d;
	}

	public function __call(string $method, array $params)
	{
		$requestId = $this->_isNotification ? null : static::$_id++;

		$response = $this->_request([
			'method' => $method,
			'params' => array_values($params),
			'id' => $requestId,
		]);

		if ($this->_isNotification)
			return true;

		if (!empty($response->error))
			throw new Exception('Request error: '. $response->error->message);

		if ($response->id != $requestId)
			throw new Exception('Unexpected responseId '. $response->id .', expected '. $requestId);

		return $response->result;
	}

	public function _request(array $data)
	{
		$raw = $this->_transport->request('POST', $this->_url, ['Content-type' => 'application/json'], json_encode($data));
		$response = json_decode($raw);

		if ($this->_enableDebug)
			print '<fieldset><legend>'.__CLASS__.'::'.__FUNCTION__.' debug output</legend><pre>'. print_r(['request'=>$data, 'response'=>$response], true). '</pre></fieldset>';

		if (false == $response)
			throw new Exception('Could not decode response as json: `%s`', [$raw]);

		return $response;
	}
}