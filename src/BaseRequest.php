<?php
/**
 * Created by PhpStorm.
 * User: KiAS
 * Date: 17.12.2020
 * Time: 9:22
 */

namespace InstagramAPI;
use InstagramAPI\Settings;

abstract class BaseRequest
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
    /**
     * @var string
     */
    protected $base_uri = "";

    /**
     * @var array
     */
    protected $string_params = [];

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return string
     */
    protected function getUrl() : string{
        return $this->base_uri.$this->buildParamsString();
    }

    /**
     * @return string
     */
    protected function buildParamsString() : string{
        $query_string = "?";

        foreach ($this->string_params as $param_name => $param_value){
            $query_string = $query_string.$param_name."=".join(",", $param_value)."&";
        }

        return $query_string."access_token=".$this->settings->getToken();
    }

    /**
     * @return array
     */
    abstract function post() : array ;

    /**
     * @param string $url
     * @return mixed
     * @throws \Exception
     */
    protected function curl(string $url){
        if(empty($url))
            throw new \Exception("empty request string");
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = json_decode(curl_exec($curl), true);

        curl_close($curl);

        return $result;
    }

    /**
     * @param string $name
     * @param array $params
     * @return BaseRequest
     */
    public function setStringParam(string $name, array $params) : BaseRequest
    {
        $this->string_params[$name] = $params;

        return $this;
    }

    /**
     * @param array $param_array
     * @param bool $need_merge
     * @return BaseRequest
     */
    public function setStringParamsArray(array $param_array, bool $need_merge = false): BaseRequest
    {
        if($need_merge){
            $param_array = array_merge($this->string_params, $param_array);
        }

        $this->string_params = $param_array;

        return $this;
    }
}