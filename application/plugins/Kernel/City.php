<?php
class Kernel_City
{
    public function __construct()
    {

    }

    public static function findCityFromUrl()
    {
        $city = false;
        $serverName = $_SERVER['SERVER_NAME'];
        $uri = substr($serverName, 0, -strlen(SITE_NAME) );

        if (!empty($uri)) {
            $city = Application_Model_Kernel_City::getByUrl( substr($uri, 0, -1) );
        }

        return $city;
    }

    public static function getUrlForLink($city)
    {
        $cityUrl = 'http://'.SITE_NAME;
        if ($city) {
            $cityUrl = 'http://'.$city->getUrl().'.'.SITE_NAME;
        }

        return $cityUrl;
    }

}