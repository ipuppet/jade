<?php


namespace AppModule\Model;


use Zimings\Jade\Module\FrameworkModule\Model\Model;

class HelloModel extends Model
{
    public function getName()
    {
        return 'jade';
    }
}