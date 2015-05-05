<?php

require __DIR__ . '/../Mindgame/Utils/bootloader.php';

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);
