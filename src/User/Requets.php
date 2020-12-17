<?php
/**
 * Created by PhpStorm.
 * User: KiAS
 * Date: 17.12.2020
 * Time: 9:21
 */

namespace InstagramAPI\User;


use InstagramAPI\BaseRequest;
use InstagramAPI\Settings;

class Requets extends BaseRequest
{
    public function __construct(Settings $settings)
    {
        parent::__construct($settings);


        $this->setStringParamsArray([
            "fields" => [
                "id",
                "username",
            ]
        ]);

        $this->base_uri = "/me";
    }

    /**
     * @return array
     */
    function post() : array
    {
        $url = $this->urlGraphAPI.$this->getUrl();

        return $this->curl($url);
    }

}