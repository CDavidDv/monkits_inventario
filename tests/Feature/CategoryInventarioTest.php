<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Tests para rutas de categorías bajo /inventario/categories.
 * Verifica que el CategoryModal.vue puede crear, listar, editar y eliminar categorías
 * usando el prefijo /inventario que axios-config.js establece como baseURL.
 */
class CategoryInventarioTest extends TestCase
{
    use DatabaseTransactions, InventoryTestTrait;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createAdminUser();
    }

    // ──────────────────────────────────────────────
    // GET /inventario/categories
    // ──────────────────────────────────────────────

    private function makeCategory(array $attrs): Category
    {
        return Category::create(array_merge([
            'color'      => '#3B82F6',
            'active'     => true,
            'created_by' => $this->user->id,
        ], $attrs));
    }

    public function test_can_list_categories_via_inventario_prefix(): void
    {
        $this->makeCategory(['name' => 'Cat Lista Test', 'type' => 'component']);

        $response = $this->actingAs($this->user)
            ->getJson('/inventario/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([['id', 'name']]);
    }

    public function test_list_categories_returns_only_active(): void
    {
        $this->makeCategory(['name' => 'Activa Test', 'type' => 'component']);
        $this->makeCategory(['name' => 'Inactiva Test', 'type' => 'component', 'active' => false]);

        $response = $this->actingAs($this->user)
            ->getJson('/inventario/categories');

        $names = array_column($response->json(), 'name');
        $this->assertContains('Activa Test', $names);
        $this->assertNotContains('Inactiva Test', $names);
    }

    public function test_list_categories_can_filter_by_type(): void
    {
        $this->makeCategory(['name' => 'Cat Component', 'type' => 'component']);
        $this->makeCategory(['name' => 'Cat Kit', 'type' => 'kit']);

        $response = $this->actingAs($this->user)
            ->getJson('/inventario/categories?type=component');

        $response->assertStatus(200);
        $names = array_column($response->json(), 'name');
        $this->assertContains('Cat Component', $names);
        $this->assertNotContains('Cat Kit', $names);
    }

    public function test_list_categories_requires_auth(): void
    {
        $response = $this->getJson('/inventario/categories');

        $response->assertStatus(401);
    }

    // ──────────────────────────────────────────────
    // POST /inventario/categories
    // ──────────────────────────────────────────────

    public function test_can_create_category_via_inventario_prefix(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/inventario/categories', [
                'name'  => 'Nueva Categoría Modal',
                'type'  => 'component',
                'color' => '#3B82F6',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('category', ['name' => 'Nueva Categoría Modal']);
    }

    public function test_create_category_returns_category_data(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/inventario/categories', [
                'name'        => 'Cat Con Descripción',
                'type'        => 'kit',
                'description' => 'Categoría de kits para test',
                'color'       => '#10B981',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('category.name', 'Cat Con Descripción');
    }

    public function test_create_category_fails_without_name(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/inventario/categories', [
                'type'  => 'component',
                'color' => '#FF0000',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['name']]);
    }

    public function test_create_category_fails_without_type(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/inventario/categories', [
                'name'  => 'Sin Tipo',
                'color' => '#FF0000',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['type']]);
    }

    public function test_create_category_fails_with_duplicate_name(): void
    {
        $this->makeCategory(['name' => 'Cat Duplicada', 'type' => 'component']);

        $response = $this->actingAs($this->user)
            ->postJson('/inventario/categories', [
                'name' => 'Cat Duplicada',
                'type' => 'component',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['name']]);
    }

    public function test_create_category_requires_auth(): void
    {
        $response = $this->postJson('/inventario/categories', [
            'name' => 'Sin Auth',
            'type' => 'component',
        ]);

        $response->assertStatus(401);
    }

    // ──────────────────────────────────────────────
    // PUT /inventario/categories/{id}
    // ──────────────────────────────────────────────

    public function test_can_update_category_via_inventario_prefix(): void
    {
        $category = $this->makeCategory(['name' => 'Cat para Actualizar', 'type' => 'component', 'color' => '#000000']);

        $response = $this->actingAs($this->user)
            ->putJson("/inventario/categories/{$category->id}", [
                'name'  => 'Cat Actualizada',
                'type'  => 'component',
                'color' => '#FF5500',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('category', [
            'id'   => $category->id,
            'name' => 'Cat Actualizada',
        ]);
    }

    public function test_update_category_fails_with_empty_name(): void
    {
        $category = $this->makeCategory(['name' => 'Cat Original', 'type' => 'component']);

        $response = $this->actingAs($this->user)
            ->putJson("/inventario/categories/{$category->id}", [
                'name' => '',
                'type' => 'component',
            ]);

        $response->assertStatus(422);
    }

    // ──────────────────────────────────────────────
    // DELETE /inventario/categories/{id}
    // ──────────────────────────────────────────────

    public function test_can_delete_category_via_inventario_prefix(): void
    {
        $category = $this->makeCategory(['name' => 'Cat para Borrar', 'type' => 'component']);
        $id = $category->id;

        $response = $this->actingAs($this->user)
            ->deleteJson("/inventario/categories/{$id}");

        $response->assertStatus(200);
        // destroy() desactiva la categoría (soft delete), no la elimina físicamente
        $this->assertDatabaseHas('category', ['id' => $id, 'active' => false]);
    }

    public function test_delete_category_requires_auth(): void
    {
        $category = $this->makeCategory(['name' => 'Cat Auth Delete', 'type' => 'component']);

        $response = $this->deleteJson("/inventario/categories/{$category->id}");

        $response->assertStatus(401);
    }

    // ──────────────────────────────────────────────
    // Rutas originales /categories siguen funcionando
    // ──────────────────────────────────────────────

    public function test_original_categories_routes_still_work(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/categories');

        $response->assertStatus(200);
    }
}
