# Openfire Symfony Bundle

Bundle used to connect to Openfire REST API and perform common tasks.

## Installation

By composer :

```
"require": {
    "jean553/symfony-openfire-bundle": "dev-master"
}
```

app/AppKernel.php :

```
$bundles = array(
    new jean553\OpenfireBundle\OpenfireBundle()
);
```

## Use

app/config/config.yml :

```
parameters:
    openfire_service: "jean553\OpenfireBundle\Services\OpenfireService"

openfire:
    url: 'http://my-openfire-server:9090/plugins/restapi/v1'
    secret: 'abcdefghijklmnopqrst'
```

In controller :

```
$service = $this->get('openfire.service');
$service->createUser('username', 'password');
$service->createChatRoom('chat_room_id'，'chat_room_name', 'username');

$userJid = $service->getJid('username');
```

## Tests

app/config/config_test.yml :

```
parameters:
    openfire_service: "jean553\OpenfireBundle\Services\DummyOpenfireService"
```
