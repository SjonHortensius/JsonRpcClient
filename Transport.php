<?php namespace TooBasic\Rpc;

interface Transport
{
	public function __construct(Transport $nextTransport = null);
	public function request(string $method, string $uri, array $headers = [], string $body = null): string;
}