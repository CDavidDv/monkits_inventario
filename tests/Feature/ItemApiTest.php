<?php

namespace Tests\Feature;

use App\Models\Item;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Tests para ItemController (API JSON).
 * Verifica: store, update, ajuste de stock, barcode e imágenes vía API.
 */
class ItemApiTest extends TestCase
{
    use DatabaseTransactions, InventoryTestTrait;

    private $user;
    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createAdminUser();
        $this->category = $this->createCategory('API Test Cat', 'component');
    }

    // ──────────────────────────────────────────────
    // POST /items (store)
    // ──────────────────────────────────────────────

    public function test_api_can_create_element_with_barcode_and_images(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/items', [
                'name'          => 'Capacitor 47uF',
                'type'          => 'element',
                'category_id'   => $this->category->id,
                'unit'          => 'Pieza',
                'min_stock'     => 10,
                'max_stock'     => 500,
                'current_stock' => 100,
                'codigo_barras' => 'CAP-47UF-001',
                'imagenes'      => 'cap47.jpg, cap47-lateral.png',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['codigo_barras' => 'CAP-47UF-001']);

        $this->assertDatabaseHas('items', [
            'name'          => 'Capacitor 47uF',
            'codigo_barras' => 'CAP-47UF-001',
            'imagenes'      => 'cap47.jpg, cap47-lateral.png',
        ]);
    }

    public function test_api_can_create_component_with_barcode(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/items', [
                'name'          => 'Componente con Barcode',
                'type'          => 'component',
                'category_id'   => $this->category->id,
                'unit'          => 'Pieza',
                'min_stock'     => 5,
                'max_stock'     => 100,
                'current_stock' => 30,
                'codigo_barras' => 'COMP-XYZ-789',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('items', ['codigo_barras' => 'COMP-XYZ-789']);
    }

    public function test_api_store_returns_422_without_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/items', [
                'name' => 'Incompleto',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_api_store_returns_422_when_max_stock_less_than_min_stock(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/items', [
                'name'          => 'Stock Inválido',
                'type'          => 'element',
                'category_id'   => $this->category->id,
                'unit'          => 'Pieza',
                'min_stock'     => 100,
                'max_stock'     => 50, // menor que min_stock
                'current_stock' => 0,
            ]);

        $response->assertStatus(422);
    }

    public function test_api_store_requires_auth(): void
    {
        $response = $this->postJson('/items', [
            'name' => 'Sin Auth',
        ]);

        $response->assertStatus(401);
    }

    // ──────────────────────────────────────────────
    // PUT /items/{id} (update)
    // ──────────────────────────────────────────────

    public function test_api_can_update_barcode(): void
    {
        $item = $this->createComponent($this->category);

        $response = $this->actingAs($this->user)
            ->putJson("/items/{$item->id}", [
                'codigo_barras' => 'UPDATED-BARCODE',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['codigo_barras' => 'UPDATED-BARCODE']);

        $this->assertDatabaseHas('items', [
            'id'            => $item->id,
            'codigo_barras' => 'UPDATED-BARCODE',
        ]);
    }

    public function test_api_can_update_imagenes(): void
    {
        $item = $this->createComponent($this->category, ['imagenes' => 'old.jpg']);

        $response = $this->actingAs($this->user)
            ->putJson("/items/{$item->id}", [
                'imagenes' => 'new1.jpg, new2.jpg, new3.png',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('items', [
            'id'       => $item->id,
            'imagenes' => 'new1.jpg, new2.jpg, new3.png',
        ]);
    }

    public function test_api_update_returns_updated_item(): void
    {
        $item = $this->createComponent($this->category);

        $response = $this->actingAs($this->user)
            ->putJson("/items/{$item->id}", [
                'name'          => 'Nombre Actualizado',
                'codigo_barras' => 'BARCODE-NEW',
                'imagenes'      => 'updated.jpg',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('item.name', 'Nombre Actualizado');
    }

    // ──────────────────────────────────────────────
    // GET /items (index)
    // ──────────────────────────────────────────────

    public function test_api_index_returns_json(): void
    {
        $this->createComponent($this->category, ['codigo_barras' => 'INDEX-TEST']);

        $response = $this->actingAs($this->user)->getJson('/items');

        $response->assertStatus(200)
            ->assertJsonStructure([['id', 'name', 'type']]);
    }

    public function test_api_index_filters_by_type(): void
    {
        $this->createComponent($this->category);
        $this->createKit($this->category);

        $response = $this->actingAs($this->user)
            ->getJson('/items?type=component');

        $response->assertStatus(200);
        $items = $response->json();
        $this->assertNotEmpty($items);
        foreach ($items as $item) {
            $this->assertEquals('component', $item['type']);
        }
    }

    // ──────────────────────────────────────────────
    // POST /items/{id}/adjust-stock
    // ──────────────────────────────────────────────

    public function test_api_can_add_stock(): void
    {
        $item = $this->createComponent($this->category, ['current_stock' => 100]);

        $response = $this->actingAs($this->user)
            ->postJson("/items/{$item->id}/adjust-stock", [
                'quantity' => 50,
                'type'     => 'add',
                'reason'   => 'Reabastecimiento',
            ]);

        $response->assertStatus(200);
        $this->assertEquals(150, (float) $response->json('new_stock'));
    }

    public function test_api_can_remove_stock(): void
    {
        $item = $this->createComponent($this->category, ['current_stock' => 100]);

        $response = $this->actingAs($this->user)
            ->postJson("/items/{$item->id}/adjust-stock", [
                'quantity' => 30,
                'type'     => 'remove',
                'reason'   => 'Uso en producción',
            ]);

        $response->assertStatus(200);
        $this->assertEquals(70, (float) $response->json('new_stock'));
    }

    public function test_api_cannot_remove_more_stock_than_available(): void
    {
        $item = $this->createComponent($this->category, ['current_stock' => 10]);

        $response = $this->actingAs($this->user)
            ->postJson("/items/{$item->id}/adjust-stock", [
                'quantity' => 99,
                'type'     => 'remove',
                'reason'   => 'Intento inválido',
            ]);

        $response->assertStatus(422);
    }

    public function test_api_can_set_stock(): void
    {
        $item = $this->createComponent($this->category, ['current_stock' => 100]);

        $response = $this->actingAs($this->user)
            ->postJson("/items/{$item->id}/adjust-stock", [
                'quantity' => 250,
                'type'     => 'set',
                'reason'   => 'Ajuste de inventario',
            ]);

        $response->assertStatus(200);
        $this->assertEquals(250, (float) $response->json('new_stock'));
    }

    // ──────────────────────────────────────────────
    // POST /items/{id}/toggle-active
    // ──────────────────────────────────────────────

    public function test_api_can_toggle_item_active(): void
    {
        $item = $this->createComponent($this->category, ['active' => true]);

        $response = $this->actingAs($this->user)
            ->postJson("/items/{$item->id}/toggle-active");

        $response->assertStatus(200);
        $this->assertDatabaseHas('items', ['id' => $item->id, 'active' => false]);
    }

    // ──────────────────────────────────────────────
    // GET /items/available-elements
    // ──────────────────────────────────────────────

    public function test_api_returns_available_elements(): void
    {
        // El endpoint filtra por type='element' por defecto
        Item::create([
            'name' => 'Elemento Disponible', 'type' => 'element',
            'category_id' => $this->category->id, 'unit' => 'Pieza',
            'min_stock' => 5, 'max_stock' => 100, 'current_stock' => 50, 'active' => true,
        ]);
        Item::create([
            'name' => 'Elemento Inactivo', 'type' => 'element',
            'category_id' => $this->category->id, 'unit' => 'Pieza',
            'min_stock' => 5, 'max_stock' => 100, 'current_stock' => 0, 'active' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/items/available-elements');

        $response->assertStatus(200);
        $names = array_column($response->json(), 'name');
        $this->assertContains('Elemento Disponible', $names);
        $this->assertNotContains('Elemento Inactivo', $names);
    }
}
