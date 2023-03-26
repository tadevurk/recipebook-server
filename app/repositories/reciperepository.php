<?php
namespace Repositories;

use PDO;
use PDOException;
use Repositories\Repository;
use Models\Recipe;


class RecipeRepository extends Repository
{
    function getAll($offset = NULL, $limit = NULL)
    {
        try {
            $query = "SELECT * FROM recipe";
            if (isset($limit) && isset($offset)) {
                $query .= " LIMIT :limit OFFSET :offset ";
            }
            $stmt = $this->connection->prepare($query);
            if (isset($limit) && isset($offset)) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
            $stmt->execute();

            $recipes = array();
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
                $recipes[] = $this->rowToRecipe($row);
            }

            return $recipes;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function getOne($id)
    {
        try {
            $query = "SELECT recipe.* FROM recipe WHERE id=:id LIMIT 1";

            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $row = $stmt->fetch();
            $recipe = $this->rowToRecipe($row);

            return $recipe;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function insert($recipe)
    {
        try {
            $stmt = $this->connection->prepare("INSERT into recipe (name, cuisine, instructions, created_at, user_id) 
            VALUES (:name,:cuisine,:instructions, now(), :user_id)");

            $stmt->execute([
                ':name' => $recipe->name,
                ':cuisine' => $recipe->cuisine,
                ':instructions' => $recipe->instructions,
                ':user_id' => $recipe->user_id
            ]);

            $this->insertRecipeIngredients($recipe, $this->connection->lastInsertId());

            return $this->getOne($recipe->id);
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function getIngredientIDByName($name)
    {
        $stmt = $this->connection->prepare("SELECT id FROM ingredients WHERE name=:name");
        $stmt->execute([
            ':name' => $name
        ]);

        if ($stmt->rowCount() > 0) {
            return $stmt->fetchColumn();
        } else {
            // ingredient is not in the ingredients database, insert it
            $stmt = $this->connection->prepare("INSERT INTO ingredients (name) VALUES (:name)");
            $stmt->execute([
                ':name' => $name
            ]);
            return $this->connection->lastInsertId();
        }
    }

    function insertRecipeIngredients($recipe, $lastInsertedID)
    {
        $stmt = $this->connection->prepare("INSERT into recipe_ingredients (recipe_id, ingredients_id, quantity, unit) 
        VALUES (:recipe_id,:ingredients_id,:quantity,:unit)");

        foreach ($recipe->ingredients as $ingredient) {
            $stmt->execute([
                'recipe_id' => $lastInsertedID,
                'ingredients_id' => $this->getIngredientIDByName($ingredient['ingredient']),
                'quantity' => $ingredient->quantity,
                'unit' => $ingredient->unit
            ]);
        }
    }

    function rowToRecipe($row)
    {
        if ($row == null) {
            return null;
        }
        $recipe = new Recipe();
        $recipe->id = $row['id'];
        $recipe->name = $row['name'];
        $recipe->instructions = $row['instructions'];
        $recipe->cuisine = $row['cuisine'];
        $recipe->created_at = $row['created_at'];
        $recipe->user_id = $row['user_id'];
        $recipe->ingredients = $this->getIngredientArray($row['id']);

        return $recipe;
    }

    function getIngredientArray($recipe_id)
    {
        try {
            $query = "Select * from recipe_ingredients where recipe_id = :recipe_id";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':recipe_id', $recipe_id);
            $stmt->execute();

            $ingredients = array();
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
                $ingredients[] = $row['ingredients_id'];
            }
            return $ingredients;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function update($recipe, $id)
    {
        try {
            $stmt = $this->connection->prepare("UPDATE recipe SET name=:name, cuisine=:cuisine, instructions=:instructions WHERE id=:id LIMIT 1");
            $stmt->execute([
                ':name' => $recipe->name,
                ':cuisine' => $recipe->cuisine,
                ':instructions' => $recipe->instructions,
                ':id' => $id
            ]);

            return $this->getOne($id);
        } catch (PDOException $e) {
            echo $e;
        }
    }

    // Updating the recipe ingredients
    function updateRecipeIngredients($recipeID, $ingredient_ID, $unit, $quantity)
    {
        $query = "UPDATE recipe_ingredients SET quantity = :quantity, unit = :unit WHERE ingredients_id = :ingredients_id AND recipe_id = :recipe_id";
        $stmt = $this->connection->prepare($query);

        $stmt->execute([
            ':quantity' => $quantity,
            ':unit' => $unit,
            ':ingredients_id' => $ingredient_ID,
            ':recipe_id' => $recipeID
        ]);
    }

    // While updating recipe, if there is a new ingredient added, insert it into the recipe_ingredients table
    function addRecipeIngredients($recipeID, $ingredient_id, $unit, $quantity)
    {
        $stmt = $this->connection->prepare("INSERT into recipe_ingredients (recipe_id, ingredients_id, quantity, unit) 
        VALUES (:recipe_id,:ingredients_id,:quantity,:unit)");

        $stmt->execute([
            'recipe_id' => $recipeID,
            'ingredients_id' => $ingredient_id,
            'quantity' => $quantity,
            'unit' => $unit
        ]);
    }

    // Delete the recipe
    function delete($id)
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM recipe WHERE id = :id");
            $stmt->execute([
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            echo $e;
        }
        return true;
    }

    // Get the recipes for autocomplete suggest 4 each time
    function getRecipesForAutocomplete(string $name)
    {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM recipe WHERE name LIKE :name LIMIT 4");
            $stmt->execute([
                ':name' => "%$name%"
            ]);

            $recipes = array();
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
                $recipes[] = $this->rowToRecipe($row);
            }

            return $recipes;
        }
        catch (PDOException $e) {
            echo $e;
        }
    }

}
?>