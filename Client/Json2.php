<?php namespace TooBasic\Rpc\Client;
use TooBasic\Rpc;
use TooBasic\Exception;

class Json2 extends Json
{
	protected $_useNamedParameters = true;

	public function useNamedParameters(bool $n = true): void
	{
		$this->_useNamedParameters = $n;
	}

	public function _request(array $data)
	{
		$data['jsonrpc'] = '2.0';

		// decode data[params] as named parameters when it consists of an array
		if ($this->_useNamedParameters && count($data['params']) == 1 && is_array($data['params'][0]))
			$data['params'] = $data['params'][0];

		return parent::_request($data);
	}
}