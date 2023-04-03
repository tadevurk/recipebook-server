<?php

namespace Models;

class Ingredient {
    public int $id;
    public string $name;
    public string $quantity;
    public string $unit;
    public recipe $recipe;
}

?>