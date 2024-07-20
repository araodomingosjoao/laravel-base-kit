<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiResponse;

trait CrudTrait
{
    protected $repository;
    protected $storeValidationRules = [];
    protected $updateValidationRules = [];
    protected $resource;

    public function create(Request $request)
    {
        $this->validateRequest($request, $this->storeValidationRules);

        $data = $request->all();
        $record = $this->repository->create($data);

        return $this->successResponse($record, 'Record created successfully', 201);
    }

    public function read($id)
    {
        $record = $this->repository->find($id);
        if ($record) {
            return $this->successResponse($record);
        }
        return ApiResponse::error('Record not found', 404);
    }

    public function update(Request $request, $id)
    {
        $this->validateRequest($request, $this->updateValidationRules);

        $data = $request->all();
        $success = $this->repository->update($id, $data);
        if ($success) {
            return $this->successResponse($success, 'Record updated successfully');
        }
        return ApiResponse::error('Record not found', 404);
    }

    public function delete($id)
    {
        $success = $this->repository->delete($id);
        if ($success) {
            return ApiResponse::success(null, 'Record deleted successfully');
        }
        return ApiResponse::error('Record not found', 404);
    }

    public function index(Request $request)
    {
        $filters = $request->query('filters', []);
        $search = $request->query('search', '');
        $perPage = $request->query('per_page', 15);
        $sortColumn = $request->query('sort_column', 'id');
        $sortDirection = $request->query('sort_direction', 'asc');

        $results = $this->repository->paginateWithFiltersAndSort($filters, $search, $perPage, $sortColumn, $sortDirection);

        return $this->successResponse($results);
    }

    protected function validateRequest(Request $request, array $rules)
    {
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            ApiResponse::error($validator->errors(), 422)->throwResponse();
        }
    }

    protected function successResponse($data, $message = 'Operation successful', $code = 200)
    {
        if ($this->resource) {
            if ($data instanceof \Illuminate\Database\Eloquent\Collection || $data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                $data = $this->resource::collection($data);
            } else {
                $data = new $this->resource($data);
            }
        }
        return ApiResponse::success($data, $message, $code);
    }
}
