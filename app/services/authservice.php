<?php
namespace Services;

use Repositories\AuthRepository;

class AuthService
{

    private $repository;

    function __construct()
    {
        $this->repository = new AuthRepository();
    }

    public function register($firstName, $lastName, $username, $password, $confirm_password)
    {
        return $this->repository->register($firstName, $lastName, $username, $password, $confirm_password);
    }

    public function checkUsernamePassword($username, $password) {
        return $this->repository->checkUsernamePassword($username, $password);
    }

}