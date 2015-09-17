<?php
namespace MBicknese\Portfolio\Models;

/**
 * Every visible page on the site is represented with a page model
 * @entity
 * @Table(name="page")
 */
class Page
{
    /**
     * @var integer
     * @id @column(type="smallint")
     * @GeneratedValue
     */
    private $id;
}

