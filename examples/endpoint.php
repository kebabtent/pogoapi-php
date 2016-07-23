<?php
use POGOAPI\Session\GoogleSession;

$session = new GoogleSession("email", "password");
$session->authenticate();
$session->setLocation(40.77878553364602, -73.96834745844728, 35); // Central Park, NY
$session->createEndpoint();
echo "Endpoint: '".$session->getEndpoint()."'\n";
