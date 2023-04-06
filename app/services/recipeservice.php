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

    public function getRecipeIngredientsByNames($names) {
        return $this->repository->getRecipeIngredientsByNames($names);
    }

    public function updateRecipeIngredients($recipeId, $ingredients) {
        return $this->repository->updateRecipeIngredients($recipeId, $ingredients);
    }

    public function deleteRecipeIngredient($recipeId, $ingredientId){
        return $this->repository->deleteRecipeIngredient($recipeId, $ingredientId);
    }

    public function insertRecipeIngredients($recipeId, $ingredients) {
        return $this->repository->insertRecipeIngredients($recipeId, $ingredients);
    }

    //TODO: duplicate code, refactor
    public function insertRecipeIngredientss($recipeId, $ingredients) {
        return $this->repository->insertRecipeIngredientss($recipeId, $ingredients);
    }
}

?>
