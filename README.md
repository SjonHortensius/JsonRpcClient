Contains a JSON-RPC and XML-RPC client; with support for SCGI. Here's an example how to use it to connect to bitcoin:

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

Any method the server supports (in this case `getInfo`), can be called as a method on the client. Here's an example how Transports can be chained for example for utorrent, to perform XMLRPC requests over SCGI:

```
<?php
spl_autoload_register(function($class){
	$class = str_replace('\\', '/', $class);
	if (0 === strpos($class, 'TooBasic/Rpc/'))
		require(__DIR__ .'/TooBasic-Rpc/'. substr($class, strlen('TooBasic/Rpc/')) .'.php');
});

$socket = new TooBasic\Rpc\Transport\Socket('127.0.0.1', 5000);
$scgi = new TooBasic\Rpc\Transport\Scgi($socket);
$client = new TooBasic\Rpc\Client\Xml('/RPC2', $scgi);

print_r($client->system->listMethods());
```