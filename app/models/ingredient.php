<?php

namespace Models;

class Ingredient {
    public int $id;
    public string $name;
    public string $quantity;
    public int $unit;
    public recipe $recipe;
}

?>