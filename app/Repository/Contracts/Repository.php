<?php

namespace App\Repository\Contracts;

/** Abstract Class for working with the model */

abstract class Repository implements RepositoryInterface
{

    protected $model;

    public function getModel()
    {
        return $this->model;
    }

    public function all($columns = array('*')): \Illuminate\Support\Collection
    {
        return collect($this->model::get($columns));
    }

    public function allDeleted($perPage = 15)
    {
        return $this->model::onlyTrashed()->simplePaginate($perPage);
    }

    public function total(): int
    {
        return (int)$this->model::get()->count();
    }

    public function count($field, $value): int
    {
        return (int)$this->model::where($field, '=', $value)->count();
    }

    public function paginate($perPage = 15)
    {
        return $this->model::simplePaginate($perPage);
    }

    public function paginateBy($field, $value, $perPage = 15)
    {
        return $this->model::where($field, '=', $value)->simplePaginate($perPage);
    }

    public function create(array $data)
    {
        return $this->model::insert($data);
    }

    public function update($field, $value, array $data)
    {
        return $this->model::where($field, '=', $value)->update($data);
    }

    public function updateOrCreate($field, $value, array $data)
    {
        return $this->model::updateOrCreate($data, [$field => $value]);
    }

    public function delete($id)
    {
        return $this->model::destroy($id);
    }

    public function deleteBy($field, $value)
    {
        return $this->model::where($field, '=', $value)->delete();
    }

    public function find($id)
    {
        return $this->model::find($id);
    }

    public function findBy($field, $value)
    {
        return $this->model::where($field, '=', $value)->first();
    }

    public function where($field, $value)
    {
        return $this->model::where($field, '=', $value);
    }

    public function deleted($field, $value)
    {
        return $this->model::onlyTrashed()->where($field, '=', $value);
    }

    public function orderBy($field, $order)
    {
        return $this->model::orderBy($field, $order);
    }

    public function with($field)
    {
        return $this->model::with($field);
    }

    public function exists($field, $value)
    {
        return $this->model::where($field, '=', $value)->exists();
    }

    public function join($db, $modelField, $dbField)
    {
        return $this->model::join($db, $modelField, '=', $dbField);
    }

    public function sum($column)
    {
        return $this->model::sum($column);
    }
}