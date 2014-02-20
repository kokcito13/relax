<?php
class Kernel_City
{

    private static $currentCityUrl = null;

    public function __construct()
    {

    }


    public static function findCityFromUrl()
    {
        if (mb_strlen($_SERVER['REQUEST_URI'], 'utf8') > 3 && mb_substr($_SERVER['REQUEST_URI'], 1, 1) !== "?") {

        }

        return false;
    }

}