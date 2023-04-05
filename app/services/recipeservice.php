<?php
namespace Services;

use Repositories\RecipeRepository;

class RecipeService {

    private $repository;

    function __construct()
    {
        $this->repository = new RecipeRepository();
    }

    public function getAll($offset = NULL, $limit = NULL) {
        return $this->repository->getAll($offset, $limit);
    }

    public function getOne($id) {
        return $this->repository->getOne($id);
    }

    public function getRecipeIngredients($id) {
        return $this->repository->getRecipeIngredients($id);
    }

    public function insert($item) {       
        return $this->repository->insert($item);      
    }

    public function update($item,$id){
        return $this->repository->update($item,$id);
    }

    public function delete($id){
        return $this->repository->delete($id);
    }

    public function getRecipesForAutocomplete($name) {
        return $this->repository->getRecipesForAutocomplete($name);
    }

    public function getIngredientsByNames($names) {
        return $this->repository->getIngredientsByNames($names);
    }

    public function updateRecipeIngredients($recipeId, $ingredients) {
        return $this->repository->updateRecipeIngredients($recipeId, $ingredients);
    }

    public function deleteRecipeIngredient($id){
        return $this->repository->deleteRecipeIngredient($id);
    }

    public function insertRecipeIngredients($recipeId, $ingredients) {
        return $this->repository->insertRecipeIngredients($recipeId, $ingredients);
    }
}

?>
