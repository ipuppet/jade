<?php


namespace AppModule\Model;


use Ipuppet\Jade\Module\FrameworkModule\Model\Model;

class HelloModel extends Model
{
    public function getName()
    {
        return 'jade';
    }
}