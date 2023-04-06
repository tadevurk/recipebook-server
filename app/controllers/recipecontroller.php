<?php
namespace Controllers;

use Exception;
use Services\RecipeService;

class RecipeController extends Controller
{
    private $service;

    // initialize services
    function __construct()
    {
        $this->service = new RecipeService();
    }

    public function getAll()
    {
        $offset = NULL;
        $limit = NULL;

        if (isset($_GET["offset"]) && is_numeric($_GET["offset"])) {
            $offset = $_GET["offset"];
        }
        if (isset($_GET["limit"]) && is_numeric($_GET["limit"])) {
            $limit = $_GET["limit"];
        }

        $recipes = $this->service->getAll($offset, $limit);
        $this->respond($recipes);
    }

    public function getOne($id)
    {
        $recipe = $this->service->getOne($id);

        // we might need some kind of error checking that returns a 404 if the recipe is not found in the DB
        if (!$recipe) {
            $this->respondWithError(404, "Recipe not found");
            return;
        }

        $this->respond($recipe);
    }

    public function getRecipeIngredients($id)
    {
        $recipe = $this->service->getRecipeIngredients($id);

        // we might need some kind of error checking that returns a 404 if the recipe is not found in the DB
        if (!$recipe) {
            $this->respondWithError(404, "Recipe not found");
            return;
        }

        $this->respond($recipe);
    }

    public function create()
    {
        try {
            $recipe = $this->createObjectFromPostedJson("Models\\Recipe");

            $ingredients = [];

            foreach ($recipe->ingredients as $ingredient) {
                $ingredients[] = ['name' => $ingredient->name, 'unit' => $ingredient->unit, 'quantity' => $ingredient->quantity];
            }

            $recipe->ingredients = $ingredients;
            // insert the recipe
            $recipe = $this->service->insert($recipe);

        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond($recipe);
    }


    public function update($id)
    {
        try {
            $_recipe = $this->createObjectFromPostedJson("Models\\Recipe");
            $updated_recipe = $this->service->update($_recipe, $_recipe->id);

            $ingredient_names = [];

            foreach ($_recipe->ingredients as $ingredient) {
                $ingredient_names[] = $ingredient->name;
            }

            // get ingredients by their names and add them to the recipe
            $db_ingredients = $this->service->getIngredientsByNames($ingredient_names);

            //$ingredientsInRecipe = $this->service->getRecipeIngredientsByNames($ingredient_names);

            $updated_ingredients = [];

            foreach ($_recipe->ingredients as $ingredient) {
                $found = false;
                foreach ($db_ingredients as $db_ingredient) {
                    if ($ingredient->name == $db_ingredient['name']) {
                        // update the unit and quantity
                        $db_ingredient['unit'] = $ingredient->unit;
                        $db_ingredient['quantity'] = $ingredient->quantity;
                        $updated_ingredients[] = $db_ingredient;
                        $this->service->updateRecipeIngredients($updated_recipe->id, (object) $db_ingredient);
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    // insert the ingredient
                    $updated_ingredients[] = ['name' => $ingredient->name, 'unit' => $ingredient->unit, 'quantity' => $ingredient->quantity];
                    //TODO: duplication
                    var_dump($updated_ingredients);
                    $this->service->insertRecipeIngredientss($_recipe, $_recipe->id);
                }
                // update the recipe with the updated ingredients
                $updated_recipe->ingredients = $updated_ingredients;
            }
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
        $this->respond($updated_recipe);
    }




    public function delete($id)
    {
        try {
            $recipe = $this->service->delete($id);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
        $this->respond($recipe);
    }

    public function getRecipesForAutocomplete()
    {
        // send the recipe name to the service as json
        $name = $this->createObjectFromPostedJson("Models\\Recipe")->name;
        $recipes = $this->service->getRecipesForAutocomplete($name);
        $this->respond($recipes);
    }


}

?>