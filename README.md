Contains a JSON-RPC and XML-RPC client; with support for SCGI. Any method the server supports (eg. `getInfo` below), can be called as a method on the client. Here's an example how to use it to connect to bitcoin:

```php
<?php
spl_autoload_register(function($class){
	$class = str_replace('\\', '/', $class);
	if (0 === strpos($class, 'TooBasic/Rpc/'))
		require(__DIR__ .'/TooBasic-Rpc/'. substr($class, strlen('TooBasic/Rpc/')) .'.php');
});

$curl = new TooBasic\Rpc\Transport\Curl;
$client = new TooBasic\Rpc\Client\Json('http://rpcUser:rpcPassword@rpcHost:rpcPort/', $curl);

print_r($client->getinfo());
```

Here's an example how Transports can be chained for example for utorrent, to perform XMLRPC requests over SCGI:

```php
<?php
// add spl_autoload_register magic

$scgi = new TooBasic\Rpc\Transport\Scgi(new TooBasic\Rpc\Transport\Socket);
$client = new TooBasic\Rpc\Client\Xml('raw://127.0.0.1:5000/RPC2', $scgi);

print_r($client->system->listMethods());
```
Here's an example how to connect to ethereum over json-rpc:
```php
<?php
// add spl_autoload_register magic

$curl = new TooBasic\Rpc\Transport\Curl;
$client = new TooBasic\Rpc\Client\Json2('http://127.0.0.1:8545', $curl);

print_r($client->eth_syncing());
```
