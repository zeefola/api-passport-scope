<?php

namespace App\Repository\Contracts;

interface RepositoryInterface
{
    public function getModel();
    public function all($columns = array('*'));
    public function allDeleted($perPage = 15);
    public function total();
    public function count($field, $value);
    public function paginate($perPage = 15);
    public function paginateBy($field, $value, $perPage = 15);
    public function create(array $data);
    public function update($field, $value, array $data);
    public function updateOrCreate($field, $value, array $data);
    public function delete($id);
    public function deleteBy($field, $value);
    public function find($id);
    public function findBy($field, $value);
    public function where($field, $value);
    public function deleted($field, $value);
    public function orderBy($field, $order);
    public function with($field);
    public function exists($field, $value);
    public function join($db, $modelField, $dbField);
    public function sum($column);
}