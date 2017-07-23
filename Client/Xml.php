<?php namespace TooBasic\Rpc\Client;
use TooBasic\Rpc;
use TooBasic\Exception;

class Xml
{
	protected $_url;
	protected $_transport;
	protected $_prefix;

	public function __construct(string$url, Rpc\Transport $transport, $methodPrefix = '')
	{
		$this->_url = $url;
		$this->_transport = $transport;
		$this->_prefix = $methodPrefix;
	}

	public function __get(string $p)
	{
		return new self($this->_url, $this->_transport, $p.'.');
	}

	public function __call(string $method, array $params)
	{
		$xml = '';
		foreach ($params as $v)
			$xml .= '<param>'. self::_encodeValue($v). '</param>';

		$request =  '<?xml version="1.0"?><methodCall>'.
			'<methodName>'.self::_encodeString($this->_prefix.$method).'</methodName>'.
			'<params>'. $xml .'</params>'.
		'</methodCall>';

		$response = $this->_transport->request('POST', $this->_url, ['Content-type' => 'text/xml'], $request);
		$response = self::_decodeResponse($response);

		return $response;
	}

	protected static function _encodeValue(string $value): string
	{
		switch (gettype($value))
		{
			case 'integer':	$xml = '<int>'. intval($value). '</int>'; break;
			case 'boolean':	$xml = '<boolean>'. intval($value) .'</boolean>'; break;
			case 'double':	$xml = '<double>'. floatval($value) .'</double>'; break;
			case 'string':	$xml = '<string>'. self::_encodeString($value) .'</string>'; break;

			case 'array':
				$data = '';
				foreach ($value as $k => $v)
				{
					if (!is_int($k))
						throw new Exception('Only simple arrays supported; use objects if you need keys');

					$data .= self::_encodeValue($v);
				}

				$xml = '<array><data>'. $data .'</data></array>';
			break;

			case 'object':
				$members = '';
				foreach ($value as $k => $v)
					$members .= '<member><name>'.self::_encodeString($k).'</name>'. self::_encodeValue($v) .'</member>';

				$xml = '<struct>'. $members .'</struct>';
			break;

			default:
				throw new Exception('Cannot encode value of type : %s', [gettype($value)]);
		}

		return '<value>' .$xml. '</value>';
	}

	protected static function _encodeString(string $s): string
	{
		// Follow the exact specs, see http://xmlrpc.scripting.com/spec.html
		return str_replace(['<', '&'], ['&lt;', '&amp;'], $s);
	}

	protected static function _decodeResponse(string $xml)
	{
		$response = simplexml_load_string($xml);
		if (false === $response)
			throw new Exception('Response cannot be interpreted as xml: %s', [var_export($xml, true)]);

		if (isset($response->fault))
			throw new Exception('Error from server: %s', [$response->fault->value->struct->member[1]->value->string]);

		return self::_decodeValue($response->params->param[0]->value);
	}

	protected static function _decodeValue(\SimpleXMLElement $xml)
	{
		$children = $xml->children();
		$child = $children[0];

		switch ($child->getName())
		{
			case 'i8':
			case 'i4':
			case 'int':
				return (int)$child;
			case 'boolean':
				return (bool)(string)$child;
			case 'double':
				return (float)$child;
			case 'string':
				return (string)$child;
			case 'array':
				$values = [];
				foreach ($child->data->value as $value)
					$values []= self::_decodeValue($value);

				return $values;
			case 'object':
				$values = new \stdClass;
				foreach ($child->member as $value)
					$values->{(string)$value->name->string} = self::_decodeValue($value->value);

				return $values;
			default:
				throw new Exception('Cannot decode value of type : %s', [$child->getName()]);
		}
	}
}