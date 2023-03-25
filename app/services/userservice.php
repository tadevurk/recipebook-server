<?php
namespace Services;

use Repositories\UserRepository;

class UserService {

    private $repository;

    function __construct()
    {
        $this->repository = new UserRepository();
    }

    function getAll($offset = NULL, $limit = NULL)
    {
        return $this->repository->getAll($offset, $limit);
    }

    function getOne($id)
    {
        return $this->repository->getOne($id);
    }

    function update($user, $id)
    {
        return $this->repository->update($user, $id);
    }

    function delete($id)
    {
        return $this->repository->delete($id);
    }
}

?>