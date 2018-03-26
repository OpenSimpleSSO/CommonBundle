# SimpleSSO CommonBundle

A bundle with common tools for the SimpleSSO server and for any Symfony client.

## Installation

Add the bundle to your Symfony project with composer.

```sh
composer require simplesso/common-bundle
```

## Configuration

The bundle provide several models that will help you to communicate with the SimpleSSO server. By default, the bundle do not declare any service. It let you choose the services you want. This documentation will show you the whole configuration. You can then adapt it to fit your needs.

### The Guard Authenticator

The bundle provide a guard that will authenticate the user against the SimpleSSO server.

First, create the `config/packages/simplesso_common.yaml` file and put the following content:

```yaml
# config/packages/simplesso_common.yaml

services:
    _defaults:
        autowire: true
        autoconfigure: true
        public: false

    SimpleSSO\CommonBundle\:
        resource: '../../vendor/simplesso/common-bundle/src/*'
        exclude: '../../vendor/simplesso/common-bundle/src/{Event,Exception,Model/Data}'

    SimpleSSO\CommonBundle\Model\AuthServerModel:
        $host: '%env(SIMPLESSO_SERVER_HOST)%'
        $publicKey: '%env(file:SIMPLESSO_SERVER_PUBLIC_KEY_PATH)%'
        $clientId: '%env(SIMPLESSO_CLIENT_ID)%'

    SimpleSSO\CommonBundle\Model\OpenSslModel:
        $privateKeyFilePath: '%env(SIMPLESSO_CLIENT_PRIVATE_KEY_PATH)%'
        $publicKeyFilePath: '%env(SIMPLESSO_CLIENT_PUBLIC_KEY_PATH)%'
```

As you can see, the services require that you set several environment variables. In development, you can add the following lines to your .env file.

```
SIMPLESSO_SERVER_HOST=https://auth.example.com
SIMPLESSO_SERVER_PUBLIC_KEY_PATH=/path/to/server-public-key.pem
SIMPLESSO_CLIENT_ID=00000000-0000-0000-0000-000000000000
SIMPLESSO_CLIENT_PUBLIC_KEY_PATH=/path/to/client-public-key.pem
SIMPLESSO_CLIENT_PRIVATE_KEY_PATH=/path/to/client-private-key.pem
```

Note that you can get the SimpleSSO server public key by opening `https://auth.example.com/public-key` in a browser or with curl. Securing public keys or the client id is not necessary. However, a real care must be taken with the client private key. The file containing the private key must be readable by the application.

Then, you'll need to configure your security.

```yaml
# config/packages/security.yaml

security:
    providers:
        # ...

    firewalls:
        main:
            anonymous: true
            provider: # ...
            guard:
                authenticators:
                    - SimpleSSO\CommonBundle\Security\AuthTokenAuthenticator
```

### The authentication route

A fallback route must be configured. The SimpleSSO server will redirect the user to this route with an **AuthToken**. For now, you cannot choose the route you want: it must be `/authenticate`.

Tag the controller provided with the bundle.

```yaml
# config/packages/simplesso_common.yaml

services:
    # ...

    SimpleSSO\CommonBundle\Controller\AuthenticationController:
        tags: [ 'controller.service_arguments' ]
```

Add an access control to your security configuration.

```yaml
# config/packages/security.yaml

security:
    firewalls:
        main: # Your firewall must be named "main".
            # ...

    access_control:
        - { path: ^/authenticate, roles: ROLE_USER }
```

Add the route to the routing. Create the file `config/routes/simplesso_common.yaml` with the following content:

```yaml
# config/routes/simplesso_common.yaml

authentication:
    path: /authenticate
    controller: SimpleSSO\CommonBundle\Controller\AuthenticationController::authenticate
```

### User and User provider

The way you store the users is completely up to you. You need to provide a user provider that will fetch the user using the id of the user. This mean you have to keep this id (which is an UUID) stored so you can fetch users authenticating.

If the user is not found by the user provider, a `authentication.unknownUserAuthenticated` event is thrown, giving you the possibility to create the user and return it to the authentication system (check `SimpleSSO\CommoneBundle\Event\UserEvent`). This is the last chance before making the authentication fail.

We provide some basic user provider, but it is recommended that you implement your own. Use them for inspiration.

#### A) The simple in memory user provider

Implementation in `SimpleSSO\CommonBundle\Security\UserProvider\InMemory`.

This simple user provider do not stores anything in a persistent way. It fetches the profile information from the SimpleSSO server API each time the user is authenticated.

To use this user provider, you need to register it as a service, and configure it in the security config:

```yaml
# config/packages/simplesso_common.yaml

services:
    # ...

    SimpleSSO\CommonBundle\Security\UserProvider\InMemory\UserProvider: ~
```

```yaml
# config/packages/security.yaml

security:
    providers:
        simple_in_memory:
            id: SimpleSSO\CommonBundle\Security\UserProvider\InMemory\UserProvider

    firewalls:
        main:
            provider: simple_in_memory # Use it in your main firewall.
            # ...
```

By default, it will instantiate a `SimpleSSO\CommonBundle\Security\UserProvider\InMemory\SimpleInMemoryUser` object to stores the user's profile. The object is then serialized in session in the security token. You can configure another class that you implemented if needed. This class must implement `SimpleSSO\CommonBundle\Security\UserProvider\SimpleSSOUserInterface`.

```yaml
# config/packages/simplesso_common.yaml

services:
    # ...

    SimpleSSO\CommonBundle\Security\UserProvider\InMemory\UserProvider:
        arguments:
            $userClass: App\Model\Data\MyCustomSimpleSSOUser
```

#### B) A Doctrine based user provider

TODO.
