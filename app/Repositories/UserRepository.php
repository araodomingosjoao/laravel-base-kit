<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    protected $relationships = [];
    protected $filterable = ['status', 'role_id'];
    protected $searchable = ['name', 'email'];

    public function __construct(User $user)
    {
        parent::__construct($user);
    }
}
