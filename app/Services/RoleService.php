<?php

namespace App\Services;

use App\Repositories\RoleRepository;

class RoleService
{
    public function __construct(
        private RoleRepository $roleRepository
    ) {}

    public function getRoleId(string $role_name){
        return $this->roleRepository->getRoleId($role_name);
    }

    public function assignUserRole(string $user_id, int $role_id){
        return $this->roleRepository->assignUserRole($user_id, $role_id);
    }
}
