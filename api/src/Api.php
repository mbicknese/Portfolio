<?php

namespace MBicknese\Portfolio;

use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use MBicknese\Portfolio\Config;

class Api
{

    /**
     * @var EntityManager Database Connection
     */
    protected $dbc;

    public function createDBC()
    {
        $this->dbc = EntityManager::create(
            array(
                'driver'   => Config::get('db_type'),
                'host'     => Config::get('db_host'),
                'user'     => Config::get('db_user'),
                'password' => Config::get('db_pass'),
                'dbname'   => Config::get('db_name'),
            ),
            Setup::createAnnotationMetadataConfiguration(
                array(Config::get('dir_model')),
                Config::get('dir_model')
            ),
            new EventManager()
        );
    }

    public function getDBC()
    {
        if (!$this->dbc) {
            $this->createDBC();
        }
        return $this->dbc;
    }
}
