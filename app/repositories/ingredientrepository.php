<?php

namespace Repositories;

use PDO;
use PDOException;
use Repositories\Repository;
use Models\Ingredient;

class IngredientRepository extends Repository
{
    function getAllByName(string $name)
    {
        try{
            $stmt = $this->connection->prepare("SELECT * FROM ingredients WHERE name LIKE :name LIMIT 4");

            $stmt->execute([
                ':name'=>"%$name%"
            ]);

            $ingredients = array();
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
                $ingredients[] = $this->rowToIngredient($row);
            }

            return $ingredients;
        }
        catch(PDOException $e){
            echo $e->getMessage();
        }
    }

    function rowToIngredient($row)
    {
        $ingredient = new Ingredient();
        $ingredient->id = $row['id'];
        $ingredient->name = $row['name'];

        return $ingredient;
    }
}