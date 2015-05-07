<?php

require __DIR__ . '/api/bootloader.php';

$api           = new MBicknese\Portfolio\Api();
$entityManager = $api->getDBC();

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);
