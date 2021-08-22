<?php


namespace App\Model;


use Ipuppet\Jade\Module\FrameworkModule\Model\Model;

class HelloModel extends Model
{
    public function getName(): string
    {
        return 'jade';
    }
}
