<?php namespace TooBasic\Rpc;

interface Transport
{
	public function request(string $method, string $uri, array $headers = [], string $body = null): string;
}