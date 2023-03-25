<?php

namespace Controllers;

use Exception;
use Services\UserService;
use \Firebase\JWT\JWT;

class UserController extends Controller
{
    private $service;

    // initialize services
    function __construct()
    {
        $this->service = new UserService();
    }

    // get all users
    function getAll()
    {
        $offset = NULL;
        $limit = NULL;

        if (isset($_GET["offset"]) && is_numeric($_GET["offset"])) {
            $offset = $_GET["offset"];
        }
        if (isset($_GET["limit"]) && is_numeric($_GET["limit"])) {
            $limit = $_GET["limit"];
        }
        $users = $this->service->getAll($offset, $limit);
        $this->respond($users);
    }

    // get one user
    function getOne($id)
    {
        $user = $this->service->getOne($id);

        // we might need some kind of error checking that returns a 404 if the user is not found in the DB
        if (!$user) {
            $this->respondWithError(404, "User not found");
            return;
        }

        $this->respond($user);
    }

    // update user
    function update($id)
    {
        try {
            $user = $this->createObjectFromPostedJson("Models\\User");
            $user = $this->service->update($user, $id);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
        $this->respond($user);
    }

    // delete user
    function delete($id)
    {
        try {
            $user = $this->service->delete($id);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
        $this->respond("User deleted");
    }
}
