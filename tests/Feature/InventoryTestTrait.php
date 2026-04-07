<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Trait con helpers compartidos para todos los tests de inventario.
 * Usa DatabaseTransactions para no afectar datos reales.
 */
trait InventoryTestTrait
{
    /**
     * Crea un usuario admin con todos los permisos necesarios.
     */
    protected function createAdminUser(): User
    {
        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $perms = [
            'manage_inventory', 'admin_access', 'manage_categories',
            'view kits', 'create kits', 'edit kits', 'delete kits', 'assemble kits',
        ];

        foreach ($perms as $perm) {
            $p = Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            $role->givePermissionTo($p);
        }

        static $counter = 0;
        $counter++;

        $user = User::create([
            'name'     => "Test Admin {$counter}",
            'email'    => "testadmin{$counter}_" . uniqid() . '@test.com',
            'password' => Hash::make('password'),
            'active'   => true,
        ]);
        $user->assignRole($role);

        // Limpiar cache de permisos de Spatie
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    /**
     * Crea una categoría de prueba.
     */
    protected function createCategory(string $name = 'Test Categoría', string $type = 'component', ?int $createdBy = null): Category
    {
        // Si no se pasa created_by, intentar inferirlo del usuario de prueba
        if ($createdBy === null) {
            $createdBy = isset($this->user) ? $this->user->id : auth()->id();
        }

        return Category::create([
            'name'        => $name,
            'description' => 'Categoría para tests',
            'color'       => '#3B82F6',
            'type'        => $type,
            'active'      => true,
            'created_by'  => $createdBy,
        ]);
    }

    /**
     * Crea un item de tipo component de prueba.
     */
    protected function createComponent(Category $category, array $overrides = []): Item
    {
        return Item::create(array_merge([
            'name' => 'Resistencia 1K Test',
            'type' => 'component',
            'category_id' => $category->id,
            'unit' => 'Pieza',
            'min_stock' => 10,
            'max_stock' => 500,
            'current_stock' => 100,
            'purchase_cost' => 0.50,
            'sale_price' => 1.00,
            'active' => true,
        ], $overrides));
    }

    /**
     * Crea un item de tipo kit de prueba.
     */
    protected function createKit(Category $category, array $overrides = []): Item
    {
        return Item::create(array_merge([
            'name' => 'Kit de Prueba',
            'type' => 'kit',
            'category_id' => $category->id,
            'unit' => 'Pieza',
            'min_stock' => 1,
            'max_stock' => 50,
            'current_stock' => 0,
            'purchase_cost' => 5.00,
            'sale_price' => 10.00,
            'active' => true,
        ], $overrides));
    }
}
