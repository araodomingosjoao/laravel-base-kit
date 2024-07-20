<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use App\Traits\CrudTrait;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use CrudTrait;

    protected $storeValidationRules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8',
    ];
    protected $updateValidationRules = [
        'name' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|email|unique:users,email',
        'password' => 'sometimes|required|string|min:8',

    protected $uniqueFields = [
        'email' => 'users'
    ];

    protected $resource = UserResource::class;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }
}
