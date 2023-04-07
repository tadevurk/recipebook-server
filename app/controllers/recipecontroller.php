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
        $_recipe = $this->createObjectFromPostedJson("Models\\Recipe");
        $this->service->update($_recipe, $_recipe->id); // Update the recipe name, cuisine, instruction

        // Get the current ingredients from the database, it has all the information of ingredients
        $currentIngredients = $this->service->getRecipeIngredients($id);

        // Get the updated ingredients from the request data, it has only the name, unit and quantity NOT ID!
        $updatedIngredients = $this->createObjectFromPostedJson("Models\\Recipe")->ingredients;

        $currentIngredientNames = [];
        $currentIngredientUnits = [];
        $currentIngredientQuantities = [];
        foreach ($currentIngredients as $currentIngredient) {
            $currentIngredientNames[] = $currentIngredient->name;
            $currentIngredientUnits[] = $currentIngredient->unit;
            $currentIngredientQuantities[] = $currentIngredient->quantity;
        }


        $updatedIngredientNames = [];
        foreach ($updatedIngredients as $updatedIngredient) {
            $updatedIngredientNames[] = $updatedIngredient->name;
        }

        // get the ingredients that are in the database but not in the request data
        $ingredientsToDeleteNames = array_diff($currentIngredientNames, $updatedIngredientNames);
        $ingredientsToDelete = $this->service->getIngredientsByNames($ingredientsToDeleteNames);

        // // delete the ingredients that are in the database but not in the request data
        foreach ($ingredientsToDelete as $ingredientToDelete) {
            $this->service->deleteRecipeIngredient($id, $ingredientToDelete['id']);
        }

        // get the name, unit, and quantity for the ingredients that are in the request data but not in the database
        $ingredientsToInsertNames = array_diff($updatedIngredientNames, $currentIngredientNames);

        // Create an array of the ingredients to insert
        $ingredientsToInsert = [];
        foreach ($updatedIngredients as $updatedIngredient) {
            if (in_array($updatedIngredient->name, $ingredientsToInsertNames)) {
                $ingredientData = [
                    'id' => $this->service->getIngredientIdByName($updatedIngredient->name),
                    'name' => $updatedIngredient->name,
                    'unit' => $updatedIngredient->unit,
                    'quantity' => $updatedIngredient->quantity
                ];
                $ingredientsToInsert[] = $ingredientData;
                $this->service->insertRecipeIngredient($id, $ingredientsToInsert);
            }
        }

        // get the name, unit, and quantity for the ingredients that are in the request data and in the database
        $ingredientsToUpdateNames = array_intersect($updatedIngredientNames, $currentIngredientNames);
        $ingredientsToUpdate = [];

        foreach ($currentIngredients as $currentIngredient) {
            if (in_array($currentIngredient->name, $ingredientsToUpdateNames)) {
                $updatedIngredient = current(array_filter($updatedIngredients, function ($ingredient) use ($currentIngredient) {
                    return $ingredient->name === $currentIngredient->name;
                }));
                $ingredientsToUpdate[] = [
                    'id' => $currentIngredient->id,
                    'name' => $updatedIngredient->name,
                    'quantity' => $updatedIngredient->quantity,
                    'unit' => $updatedIngredient->unit
                ];
            }
        }
        foreach ($ingredientsToUpdate as $ingredientToUpdate) {
            $this->service->updateRecipeIngredients($id, $ingredientToUpdate);
        }

        // get the updated recipe from the database
        $updatedRecipe = $this->service->getOne($id);

        $this->respond($updatedRecipe);
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