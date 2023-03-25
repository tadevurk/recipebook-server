<?php

namespace Repositories;

use PDO;
use PDOException;
use Repositories\Repository;
use Models\User;

class AuthRepository extends Repository
{
    function register($firstName, $lastName, $username, $password, $confirm_password)
    {
        try {
            //check if the password and confirm password match
            if ($password != $confirm_password) {
                return false;
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // insert the user into the database
            $stmt = $this->connection->prepare('INSERT INTO user (firstname, lastname, hashed_password, role, username)
            VALUES (:firstname, :lastname, :hashed_password,:role, :username)');

            $stmt->execute([
                'firstname' => $firstName,
                'lastname' => $lastName,
                'hashed_password' => $hashed_password,
                'role' => 1,
                'username' => $username
            ]);

            return new User();
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function checkUsernamePassword($username, $password)
    {
        try {
            // retrieve the user with the given username
            $stmt = $this->connection->prepare("SELECT id, firstname, lastname, hashed_password, role, username FROM user WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, 'Models\User');
            $user = $stmt->fetch();

            // verify if the password matches the hash in the database
            $result = $this->verifyPassword($password, $user->hashed_password);

            if (!$result)
                return false;

            // do not pass the password hash to the caller
            $user->hashed_password = "";

            return $user;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    // verify the password hash
    function verifyPassword($input, $hash)
    {
        return password_verify($input, $hash);
    }
}