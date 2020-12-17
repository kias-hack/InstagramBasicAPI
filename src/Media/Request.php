<?php
/**
 * Created by PhpStorm.
 * User: KiAS
 * Date: 16.12.2020
 * Time: 12:50
 */

namespace InstagramAPI\Media;


use InstagramAPI\Settings;

class Request
{
    /**
     * @var Settings
     */
    protected $settings;
    /**
     * @var string
     */
    protected $urlAPI = "https://api.instagram.com";
    /**
     * @var string
     */
    protected $urlGraphAPI = "https://graph.instagram.com";
    /**
     * @description возможные поля
     * @var array
     */
    protected $fields = [
        'caption',
        'id',
        'media_type',
        'media_url',
        'permalink',
        'thumbnail_url',
        'timestamp',
        'username'
    ];

    /**
     * @var string
     */
    protected $base_uri = "/me/media";

    /**
     * Request constructor.
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    private function getUrl() : string {
        return $this->urlGraphAPI.$this->base_uri;
    }

    private function getQuery() : string {
        $token = $this->settings->getToken();

        return "?fields=".join(",", $this->fields)."&access_token=".$token;
    }

    public function post() : array{
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $this->getUrl().$this->getQuery());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = json_decode(curl_exec($curl), 1);

        curl_close($curl);

        return $result;
    }

}