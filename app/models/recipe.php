<?php

nameSpace Models;

class Recipe {
    public int $id;
    public string $name;
    public string $instructions;
    public string $cuisine;
    public string $created_at;
    public int $user_id;
    public array $ingredients;
}
?>