<?php


namespace Zimings\Jade\Plugins;


class Regex
{
    const phone = '/^((13[0-9])|(14[579])|(15[^4])|(18[0-9])|(17[0135678]|18[0-9]|19[8]))\d{8}$/';
    const email = '/[A-Za-z\d]+([-_.][A-Za-z\d]+)*@([A-Za-z\d]+[-.])+[A-Za-z\d]{2,4}/';
}