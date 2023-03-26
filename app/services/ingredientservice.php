<?php

namespace Services;

use Repositories\IngredientRepository;

class IngredientService
{

    private $repository;

    function __construct()
    {
        $this->repository = new IngredientRepository();
    }

    public function getAll(string $name)
    {
        return $this->repository->getAllByName($name);
    }
}