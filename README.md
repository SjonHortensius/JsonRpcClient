This client contains as little code as possible. Here's an example how to use it to connect to bitcoin:

```php
<?php
require('JsonRpcClient.php');

$client = new JsonRpcClient('http://rpcUser:rpcPassword@rpcHost:rpcPort/');

print_r($client->getinfo());
```

You can call any method the server supports on $client.