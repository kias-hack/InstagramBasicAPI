<?php
/**
 * Created by PhpStorm.
 * User: KiAS
 * Date: 16.12.2020
 * Time: 16:21
 */

namespace InstagramAPI\Token;


use InstagramAPI\Settings;

class Request
{
    /**
     * @var string
     */
    protected $urlAPI = "https://api.instagram.com";
    /**
     * @var string
     */
    protected $urlGraphAPI = "https://graph.instagram.com";

    /**
     * @var Settings
     */
    protected $settings;

    protected $base_uris = [
        "refresh" => "/refresh_access_token",
    ];

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function refresh() : array {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $this->getUrl().$this->getQuery());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = json_decode(curl_exec($curl), 1);
        d($result);

        curl_close($curl);

        return $result;
    }

    public function getUrl() : string{
        return $this->urlGraphAPI.$this->base_uris["refresh"];
    }

    public function getQuery() : string{
//        return "";
        return "?grant_type=ig_refresh_token&access_token=".$this->settings->getToken();
    }
}