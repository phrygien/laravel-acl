<?php

namespace MecenePhrygien\LaravelAcl\Contracts;

interface HasAcl
{
    public function roles();
    public function hasRole(string $slug): bool;
    public function hasAnyRole(array $slugs): bool;
    public function hasAllRoles(array $slugs): bool;
    public function hasPermission(string $slug): bool;
    public function hasAnyPermission(array $slugs): bool;
    public function hasAllPermissions(array $slugs): bool;
    public function assignRole(string|object $role): static;
    public function removeRole(string|object $role): static;
    public function syncRoles(array $slugs): static;
    public function clearAclCache(): void;
}