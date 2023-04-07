<?php

namespace Controllers;

use Exception;
use Services\AuthService;
use Services\UserService;
use \Firebase\JWT\JWT;

class AuthController extends Controller
{
    private $service;
    private $userService;

    // initialize services
    function __construct()
    {
        $this->service = new AuthService();
        $this->userService = new UserService();
    }

    public function login()
    {
        // read user data from request body
        $postedUser = $this->createObjectFromPostedJson("Models\\User");

        // get user from db
        $user = $this->service->checkUsernamePassword($postedUser->username, $postedUser->password);

        // if the method returned false, the username and/or password were incorrect
        if (!$user) {
            $this->respondWithError(401, "Invalid login");
            return;
        }

        // generate jwt
        $tokenResponse = $this->generateJwt($user);

        $data = array();

        $data["token"] = $tokenResponse;
        $data["user"] = $user;

        $this->respond($data);
    }



    function register()
    {
        try {
            $user = $this->createObjectFromPostedJson("Models\\Auth");
            $user = $this->service->register($user->firstName, $user->lastName, $user->username, $user->password, $user->confirm_password);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
        $this->respond($user);
    }

    function generateJwt($user)
    {
        $secret_key = "YOUR_SECRET_KEY";

        $issuer = "THE_ISSUER"; // this can be the domain/servername that issues the token
        $audience = "THE_AUDIENCE"; // this can be the domain/servername that checks the token

        $issuedAt = time(); // issued at
        $notbefore = $issuedAt; //not valid before 
        $expire = $issuedAt + 600; // expiration time is set at +600 seconds (10 minutes)

        // JWT expiration times should be kept short (10-30 minutes)
        // A refresh token system should be implemented if we want clients to stay logged in for longer periods

        // note how these claims are 3 characters long to keep the JWT as small as possible
        $payload = array(
            "iss" => $issuer,
            "aud" => $audience,
            "iat" => $issuedAt,
            "nbf" => $notbefore,
            "exp" => $expire,
            "data" => array(
                "id" => $user->id,
                "username" => $user->username
            )
        );

        $jwt = JWT::encode($payload, $secret_key, 'HS256');

        return
            array(
                "message" => "Successful login.",
                "jwt" => $jwt,
                "username" => $user->username,
                "expireAt" => $expire
            );
    }

}