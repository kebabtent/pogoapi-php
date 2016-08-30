# POGOAPI-PHP
API for Pokemon Go

## Progress
* [x] Login with google
* [x] Login with PTC
* [x] Uk6 compatible
* [x] Obtain endpoint
* [x] Obtain profile
* [x] Obtain map objects (pokemons/pokestops/gyms)

## Installation
Add the following fields in your project `composer.json`:
``` json
{
  "require": {
    "jaspervdm/pogoapi-php": "dev-master"
  },
  "minimum-stability": "dev",
  "prefer-stable": true
}
```

## Usage
``` php
// First set up some logger
$logger = new \Monolog\Logger("POGOAPI");

// Set initial location
$location = new \POGOAPI\Map\Location(LATITUDE, LONGITUDE, ALTITUDE);

// Create a Session instance
$session = new \POGOAPI\Session\GoogleSession($logger, $location, USERNAME, PASSWORD);
$session->authenticate();
$session->createEndpoint();

// At this point one can communicate with the pokemon go servers, for example:
$profile = $session->getProfile();
echo "My username is ".$profile->getUsername()."\n";
```
See also the `examples/` directory

## Contributions
* [jaspervdm](https://github.com/jaspervdm)
* [barryvdh](https://github.com/barryvdh)

## Credits
* [AeonLucid](https://github.com/AeonLucid) for the [proto files](https://github.com/AeonLucid/POGOProtos)
* [POGO-PHP](https://github.com/POGO-PHP) for the [pure PHP implementation of encrypt.c](https://github.com/POGO-PHP/POGOEncrypt-PHP)
* [Sjaakmoes](https://github.com/Sjaakmoes) for the [correct implementation of request signing](https://github.com/Sjaakmoes/pokapi/blob/27cb0281500821b7b2c64150aa779a5a997080c3/src/Pokapi/Rpc/Service.php#L243)
* [MatthewKingDev](https://github.com/MatthewKingDev) for the [modified xxhash](https://github.com/MatthewKingDev/php-xxhash)

