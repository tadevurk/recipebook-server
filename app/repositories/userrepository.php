<?php

namespace Repositories;

use PDO;
use PDOException;
use Repositories\Repository;
use Models\User;

class UserRepository extends Repository
{
    function getAll($offset = NULL, $limit = NULL)
    {
        try {
            $query = "SELECT * FROM user";
            if (isset($limit) && isset($offset)) {
                $query .= " LIMIT :limit OFFSET :offset ";
            }
            $stmt = $this->connection->prepare($query);
            if (isset($limit) && isset($offset)) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
            $stmt->execute();

            $users = array();
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
                $users[] = $this->rowToUser($row);
            }

            return $users;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function getOne($id)
    {
        try {
            $query = "SELECT user.* FROM user WHERE id=:id LIMIT 1";

            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $row = $stmt->fetch();
            $user = $this->rowToUser($row);

            return $user;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function update($user, $id)
    {
        try{
            $stmt = $this->connection->prepare("UPDATE user SET firstname=:firstname, lastname=:lastname, username=:username WHERE id=:id LIMIT 1");
            $stmt->execute([
                ':firstname' => $user->firstname,
                ':lastname' => $user->lastname,
                ':username' => $user->username,
                ':id' => $id
            ]);

            return $this->getOne($id);
        } catch (PDOException $e) {
            echo $e;
        }

    }

    function delete($id)
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM user WHERE id=:id");
            $stmt->execute([
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            echo $e;
        }

        return true;
    }

    function rowToUser($row)
    {
        $user = new User();
        $user->id = $row['id'];
        $user->firstname = $row['firstname'];
        $user->lastname = $row['lastname'];
        $user->username = $row['username'];
        $user->role = $row['role'];
        $user->hashed_password = $row['hashed_password'];

        return $user;
    }
}