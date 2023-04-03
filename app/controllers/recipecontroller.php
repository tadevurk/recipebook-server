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

    public function getRecipeIngredients($id){
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
            $recipe = $this->service->insert($recipe);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond($recipe);
    }

    public function update($id)
    {
        try {
            $recipe = $this->createObjectFromPostedJson("Models\\Recipe");
            $recipe = $this->service->update($recipe, $id);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
        $this->respond($recipe);
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
        $name = $this->createObjectFromPostedJson("Models\\Recipe")->name;
        $recipes = $this->service->getRecipesForAutocomplete($name);
        $this->respond($recipes);
    }
}

?>