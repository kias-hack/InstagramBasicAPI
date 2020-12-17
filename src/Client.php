<?php
/**
 * Created by PhpStorm.
 * User: KiAS
 * Date: 16.12.2020
 * Time: 12:17
 */

namespace InstagramAPI;

use InstagramAPI\Media\Request;
use InstagramAPI\Settings;
use InstagramAPI\Token;

class Client
{
    /**
     * @var \InstagramAPI\Token\Request
     */
    protected $tokenNode;
    /**
     * @var Media\Request
     */
    protected $mediaNode;
    /**
     * @var User\Requets
     */
    protected $userNode;
    /**
     * @var \DateTime
     */
    protected $current_date;
    /**
     * @var \InstagramAPI\Settings
     */
    protected $settings;
    /**
     * @var string
     */
    protected $url_instagram = "https://www.instagram.ru";

    /**
     * Client constructor.
     * @param InstagramAPI\Settings
     * @throws \Exception
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
        $this->mediaNode = new Request($this->settings);
        $this->userNode = new User\Requets($this->settings);

        $this->current_date = new \DateTime();

        /**
         * Если токен существует больше 59 дней, то он мертвый
         */
        if((clone $settings->getDateCreateToken())->modify("+59 days") < $this->current_date)
            throw new \Exception("token is die");

        if(!$this->settings->isCanUpdateToken())
            return $this;

        /**
         * Если токен живет больше времени через которое нужно обновлять его, то обновляем
         * в противном случае ничего не делаем
         */
        if(
            ($this->settings->getDateToUpdateToken() <= $this->current_date)
            && is_callable($this->settings->getCallbackUpdateToken())
        ) {
            /**
             * @description обновляем токен
             */
            $this->settings->setToken($this->refreshToken());
            /**
             * @description вызываем callback функцию
             */
            $this->settings->getCallbackUpdateToken()($this->settings->getToken(), $this->current_date);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    protected function refreshToken(){
        $this->tokenNode = new Token\Request($this->settings);

        return $this->tokenNode->refresh()["access_token"];
    }

    /**
     * @return array
     */
    function getMedia() : array{
        return $this->mediaNode->post();
    }

    /**
     * @return array
     */
    function getUserData() : array{
        $data = $this->userNode->post();
        $data["link"] = $this->url_instagram."/".$data["username"];

        return $data;
    }
}