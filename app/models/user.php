<?php
namespace Models;

class User
{
    public int $id;
    public string $firstname;
    public string $lastname;
    public string $username;
    public int $role;
    public string $password;

    public string $hashed_password;
}

?>