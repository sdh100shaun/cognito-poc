<?php

declare(strict_types=1);

namespace App;


use Dotenv\Dotenv;

class Configuration
{
    private $region;
    private $clientId;
    /**
     * @var Dotenv
     */
    private $dotenv;

    public function __construct(Dotenv $dotenv)
    {
        $this->dotenv = $dotenv;
        $this->dotenv->load();

        $this->region = $_ENV['region']??'';
        $this->clientId = $_ENV['clientId']??'';
        $this->userpoolId = $_ENV['userpoolId']??'';

    }
    /**
     * @return mixed
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param mixed $region
     */
    public function setRegion($region): void
    {
        $this->region = $region;
    }

    /**
     * @return mixed
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param mixed $clientId
     */
    public function setClientId($clientId): void
    {
        $this->clientId = $clientId;
    }

    /**
     * @return mixed
     */
    public function getUserpoolId()
    {
        return $this->userpoolId;
    }

    /**
     * @param mixed $userpoolId
     */
    public function setUserpoolId($userpoolId): void
    {
        $this->userpoolId = $userpoolId;
    }
    private $userpoolId;


}