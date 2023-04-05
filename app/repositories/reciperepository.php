<?php
namespace Repositories;

use PDO;
use PDOException;
use Repositories\Repository;
use Models\Recipe;
use Models\Ingredient;


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

    function rowToIngredient($row)
    {
        $ingredient = new Ingredient();
        $ingredient->id = $row['id'];
        $ingredient->name = $row['name'];
        $ingredient->quantity = $row['quantity'];
        $ingredient->unit = $row['unit'];

        return $ingredient;
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

            $recipe->id = $this->connection->lastInsertId();
            $this->insertRecipeIngredients($recipe, $recipe->id);

            return $this->getOne($recipe->id);
        } catch (PDOException $e) {
            echo $e;
        }
    }


    function getRecipeIngredients($id)
    {
        try {
            $query = "SELECT ingredients.id, ingredients.name as name, recipe_ingredients.quantity as quantity, recipe_ingredients.unit as unit FROM `recipe_ingredients` 
            join recipe on recipe.id = recipe_ingredients.recipe_id join ingredients on ingredients.id = recipe_ingredients.ingredients_id
            WHERE recipe_ingredients.recipe_id = :id";

            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $ingredients = array();
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
                $ingredients[] = $this->rowToIngredient($row);
            }

            return $ingredients;
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
                'ingredients_id' => $this->getIngredientIDByName($ingredient['name']),
                'quantity' => $ingredient['quantity'],
                'unit' => $ingredient['unit']
            ]);
        }
    }


    function deleteRecipeIngredient($id)
    {
        $stmt = $this->connection->prepare("DELETE FROM recipe_ingredients WHERE recipe_ingredients.recipe_id = :id");
        $stmt->execute([
            ':id' => $id
        ]);
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
    // function updateRecipeIngredients($recipeId, $ingredients)
    // {
    //     $query = "UPDATE recipe_ingredients SET quantity = :quantity, unit = :unit WHERE ingredients_id = :ingredients_id AND recipe_id = :recipe_id";
    //     $stmt = $this->connection->prepare($query);

    //     foreach ($ingredients as $ingredient) {
    //         $stmt->execute([
    //             ':quantity' => $ingredient['quantity'],
    //             ':unit' => $ingredient['unit'],
    //             ':ingredients_id' => $ingredient['id'],
    //             ':recipe_id' => $recipeId
    //         ]);
    //     }
    // }

    function updateRecipeIngredients($recipeId, $ingredient)
    {
        $query = "UPDATE recipe_ingredients SET quantity = :quantity, unit = :unit WHERE ingredients_id = :id AND recipe_id = :recipe_id";
        $stmt = $this->connection->prepare($query);

        $stmt->execute([
            ':quantity' => $ingredient->quantity,
            ':unit' => $ingredient->unit,
            ':id' => $ingredient->id,
            ':recipe_id' => $recipeId
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
    function getRecipesForAutocomplete($name)
    {
        try {
            $stmt = $this->connection->prepare("SELECT id, name FROM recipe WHERE name LIKE :name LIMIT 4");
            $stmt->execute([
                ':name' => "%$name%"
            ]);

            $recipes = array();
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
                //return row just id and name
                $recipes[] = $row;
            }

            return $recipes;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function getIngredientsByNames($names)
    {
        try {
            $placeholders = implode(',', array_fill(0, count($names), '?'));
            $query = "SELECT * FROM ingredients WHERE name IN ($placeholders)";

            $stmt = $this->connection->prepare($query);
            $stmt->execute($names);

            $ingredients = array();
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
                $ingredients[] = $row;
            }

            return $ingredients;
        } catch (PDOException $e) {
            echo $e;
        }
    }

}
?>