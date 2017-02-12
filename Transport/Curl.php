<?php namespace TooBasic\Rpc\Transport;
use TooBasic\Rpc;
use TooBasic\Exception;

class Curl implements Rpc\Transport
{
	public function __construct(Rpc\Transport $transport = null)
	{
		if (isset($transport))
			throw new Exception('Curl transport does not support chaining');
	}

	public function request(string $method, string $url, array $headers = [], string $body = null): string
	{
		array_walk($headers, function(&$v, $k){
			$v = $k .': '. $v;
		});

		$c = curl_init($url);

		try
		{
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