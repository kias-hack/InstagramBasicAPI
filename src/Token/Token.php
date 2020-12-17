<?php
/**
 * Created by PhpStorm.
 * User: KiAS
 * Date: 16.12.2020
 * Time: 16:56
 */

namespace InstagramAPI\Token;

class Token implements \Serializable
{
    /**
     * @var \DateTime
     */
    protected $date_create;
    /**
     * @var string
     */
    protected $token;

    public function __construct(string $token, \DateTime $dateCreate){
        if(empty($token))
            throw new \Exception("token not exists");

        $this->token = $token;
        $this->date_create = $dateCreate;
    }

    /**
     * @param string $token
     * @throws \Exception
     */
    public function setToken(string $token)
    {
        if(empty($token))
            throw new \Exception("empty token");

        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param \DateTime $date_create
     */
    public function setDateCreate(\DateTime $date_create)
    {
        $this->date_create = $date_create;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreate(): \DateTime
    {
        return $this->date_create;
    }

    public function serialize()
    {
        return serialize([
            "token" => $this->token,
            "create_date" => $this->date_create
        ]);
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->setToken($data["token"]);
        $this->setDateCreate($data["create_date"]);
    }
}