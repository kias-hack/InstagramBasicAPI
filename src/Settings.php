<?php
/**
 * Created by PhpStorm.
 * User: KiAS
 * Date: 16.12.2020
 * Time: 13:50
 */

namespace InstagramAPI;


use InstagramAPI\Token\Token;

class Settings
{
    /**
     * @var Token
     */
    protected $tokenData;
    /**
     * @var string
     */
    protected $token;
    /**
     * @var bool
     */
    protected $canUpdateToken;
    /**
     * @description выызвается после обновления токена
     * @param string $token
     * @param \DateTime $date_create
     * @var callable
     */
    protected $callbackUpdateToken;
    /**
     * @description дата создания токена
     * @var \DateTime
     */
    protected $dateCreateToken;
    /**
     * @description Дата обновления токена
     * @var \DateTime
     */
    protected $dateToUpdateToken;


    public function __construct(Token $token,\DateTime $dateCreate = Null,bool $canRefreshToken = false,\DateTime $dateRefresh = Null, callable $callback_refresh_token = Null)
    {
        $this->tokenData = $token;
        $this->setCanUpdateToken($canRefreshToken);

        if(!$canRefreshToken)
            return $this;

        $this->setCallbackUpdateToken($callback_refresh_token)
            ->setDateToUpdateToken($dateRefresh)
        ;
    }

    /**
     * @param \DateTime $dateCreateToken
     * @return Settings
     */
    public function setDateCreateToken(\DateTime $dateCreateToken) : Settings
    {
        $this->tokenData->setToken($dateCreateToken);

        return $this;
    }

    /**
     * @param bool $canUpdateToken
     * @return Settings
     */
    public function setCanUpdateToken(bool $canUpdateToken) : Settings
    {
        $this->canUpdateToken = $canUpdateToken;

        return $this;
    }

    /**
     * @param callable $callable
     * @return Settings
     */
    public function setCallbackUpdateToken(callable $callable) : Settings
    {
        $this->callbackUpdateToken = $callable;

        return $this;
    }

    /**
     * @param string $token
     * @return Settings
     */
    public function setToken(string $token) : Settings
    {
        $this->tokenData->setToken($token);

        return $this;
    }

    /**
     * @param \DateTime $dateToUpdateToken
     * @return Settings
     */
    public function setDateToUpdateToken(\DateTime $dateToUpdateToken) : Settings
    {
        $this->dateToUpdateToken = $dateToUpdateToken;

        return $this;
    }

    /**
     * @return callable
     * @throws \Exception
     */
    public function getCallbackUpdateToken() : callable
    {
        if(!is_callable($this->callbackUpdateToken))
            throw new \Exception("callback is not callable or not setup");

        return $this->callbackUpdateToken;
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    public function getDateCreateToken() : \DateTime
    {
        return $this->tokenData->getDateCreate();
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    public function getDateToUpdateToken() : \DateTime
    {
        if(!($this->dateToUpdateToken instanceof \DateTime))
            throw new \Exception("empty date update token");

        return $this->dateToUpdateToken;
    }

    /**
     * @return bool
     */
    public function isCanUpdateToken(): bool
    {
        return $this->canUpdateToken;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getToken(): string
    {
        return $this->tokenData->getToken();
    }
}