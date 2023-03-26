<?php

namespace Controllers;

use Exception;
use Services\IngredientService;

class IngredientController extends Controller
{
    private $service;

    // initialize services
    function __construct()
    {
        $this->service = new IngredientService();
    }

    public function getAll()
    {
        $name = $this->createObjectFromPostedJson("Models\\Ingredient")->name;
        $ingredients = $this->service->getAll($name);
        $this->respond($ingredients);
    }
}