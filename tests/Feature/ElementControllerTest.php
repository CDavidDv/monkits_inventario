<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Tests para ElementController (CRUD de componentes).
 * Verifica: crear, leer, actualizar, eliminar + código de barras + imágenes.
 */
class ElementControllerTest extends TestCase
{
    use DatabaseTransactions, InventoryTestTrait;

    private $user;
    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createAdminUser();
        $this->category = $this->createCategory('Electrónicos Test', 'component');
    }

    // ──────────────────────────────────────────────
    // GET /elements/create
    // ──────────────────────────────────────────────

    public function test_create_page_loads_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)->get('/elements/create');

        $response->assertStatus(200);
    }

    public function test_create_page_requires_authentication(): void
    {
        $response = $this->get('/elements/create');

        $response->assertRedirect('/login');
    }

    // ──────────────────────────────────────────────
    // POST /elements (store)
    // ──────────────────────────────────────────────

    public function test_can_create_element_with_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post('/elements', [
            'name'        => 'Condensador 100uF',
            'category_id' => $this->category->id,
            'unit'        => 'Pieza',
            'min_stock'   => 10,
            'max_stock'   => 200,
            'current_stock' => 50,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('items', [
            'name' => 'Condensador 100uF',
            'type' => 'component',
        ]);
    }

    public function test_can_create_element_with_codigo_barras(): void
    {
        $response = $this->actingAs($this->user)->post('/elements', [
            'name'           => 'LED Rojo 5mm',
            'category_id'    => $this->category->id,
            'unit'           => 'Pieza',
            'min_stock'      => 5,
            'max_stock'      => 1000,
            'current_stock'  => 200,
            'codigo_barras'  => '7501234567890',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('items', [
            'name'          => 'LED Rojo 5mm',
            'codigo_barras' => '7501234567890',
        ]);
    }

    public function test_can_create_element_with_imagenes(): void
    {
        $response = $this->actingAs($this->user)->post('/elements', [
            'name'          => 'Transistor NPN',
            'category_id'   => $this->category->id,
            'unit'          => 'Pieza',
            'min_stock'     => 20,
            'max_stock'     => 500,
            'current_stock' => 100,
            'imagenes'      => 'transistor.jpg, transistor-detalle.png',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('items', [
            'name'     => 'Transistor NPN',
            'imagenes' => 'transistor.jpg, transistor-detalle.png',
        ]);
    }

    public function test_can_create_element_with_all_new_fields(): void
    {
        $response = $this->actingAs($this->user)->post('/elements', [
            'name'           => 'Resistencia 10K',
            'description'    => 'Resistencia de carbón 10K 1/4W',
            'category_id'    => $this->category->id,
            'unit'           => 'Pieza',
            'min_stock'      => 50,
            'max_stock'      => 5000,
            'current_stock'  => 500,
            'purchase_cost'  => 0.10,
            'sale_price'     => 0.25,
            'location'       => 'Estante B2',
            'codigo_barras'  => 'RES-10K-001',
            'imagenes'       => 'res10k.jpg, res10k-back.jpg, res10k-codigo.png',
        ]);

        $response->assertRedirect();

        $item = Item::where('name', 'Resistencia 10K')->first();
        $this->assertNotNull($item);
        $this->assertEquals('RES-10K-001', $item->codigo_barras);
        $this->assertEquals('res10k.jpg, res10k-back.jpg, res10k-codigo.png', $item->imagenes);
        $this->assertEquals('Estante B2', $item->location);
    }

    public function test_store_fails_without_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post('/elements', [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_fails_with_invalid_category(): void
    {
        $response = $this->actingAs($this->user)->post('/elements', [
            'name'        => 'Item Inválido',
            'category_id' => 99999,
            'unit'        => 'Pieza',
            'min_stock'   => 0,
            'max_stock'   => 100,
        ]);

        $response->assertSessionHasErrors('category_id');
    }

    // ──────────────────────────────────────────────
    // GET /elements/{id} (show)
    // ──────────────────────────────────────────────

    public function test_can_view_element_detail(): void
    {
        $element = $this->createComponent($this->category, [
            'codigo_barras' => 'TEST-001',
            'imagenes'      => 'foto.jpg',
        ]);

        $response = $this->actingAs($this->user)->get("/elements/{$element->id}");

        $response->assertStatus(200);
    }

    // ──────────────────────────────────────────────
    // GET /elements/{id}/edit + PUT /elements/{id}
    // ──────────────────────────────────────────────

    public function test_edit_page_loads_for_existing_element(): void
    {
        $element = $this->createComponent($this->category);

        $response = $this->actingAs($this->user)->get("/elements/{$element->id}/edit");

        $response->assertStatus(200);
    }

    public function test_can_update_element_codigo_barras(): void
    {
        $element = $this->createComponent($this->category);

        $response = $this->actingAs($this->user)->put("/elements/{$element->id}", [
            'name'          => $element->name,
            'category_id'   => $this->category->id,
            'unit'          => $element->unit,
            'min_stock'     => $element->min_stock,
            'max_stock'     => $element->max_stock,
            'codigo_barras' => 'NUEVO-BARCODE-123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('items', [
            'id'            => $element->id,
            'codigo_barras' => 'NUEVO-BARCODE-123',
        ]);
    }

    public function test_can_update_element_imagenes(): void
    {
        $element = $this->createComponent($this->category, ['imagenes' => 'vieja.jpg']);

        $response = $this->actingAs($this->user)->put("/elements/{$element->id}", [
            'name'        => $element->name,
            'category_id' => $this->category->id,
            'unit'        => $element->unit,
            'min_stock'   => $element->min_stock,
            'max_stock'   => $element->max_stock,
            'imagenes'    => 'nueva1.jpg, nueva2.png',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('items', [
            'id'       => $element->id,
            'imagenes' => 'nueva1.jpg, nueva2.png',
        ]);
    }

    public function test_can_clear_imagenes_field(): void
    {
        $element = $this->createComponent($this->category, ['imagenes' => 'foto.jpg']);

        $this->actingAs($this->user)->put("/elements/{$element->id}", [
            'name'        => $element->name,
            'category_id' => $this->category->id,
            'unit'        => $element->unit,
            'min_stock'   => $element->min_stock,
            'max_stock'   => $element->max_stock,
            'imagenes'    => '',
        ]);

        $this->assertDatabaseHas('items', [
            'id'       => $element->id,
            'imagenes' => null,
        ]);
    }

    // ──────────────────────────────────────────────
    // DELETE /elements/{id}
    // ──────────────────────────────────────────────

    public function test_can_delete_element(): void
    {
        $element = $this->createComponent($this->category);
        $id = $element->id;

        $response = $this->actingAs($this->user)->delete("/elements/{$id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('items', ['id' => $id]);
    }

    // ──────────────────────────────────────────────
    // Campos en base de datos
    // ──────────────────────────────────────────────

    public function test_items_table_has_codigo_barras_column(): void
    {
        $element = $this->createComponent($this->category, ['codigo_barras' => 'BARCODE-TEST']);

        $this->assertEquals('BARCODE-TEST', $element->fresh()->codigo_barras);
    }

    public function test_items_table_has_imagenes_column(): void
    {
        $element = $this->createComponent($this->category, ['imagenes' => 'img1.jpg, img2.jpg']);

        $this->assertEquals('img1.jpg, img2.jpg', $element->fresh()->imagenes);
    }

    public function test_codigo_barras_and_imagenes_can_be_null(): void
    {
        $element = $this->createComponent($this->category);

        $this->assertNull($element->fresh()->codigo_barras);
        $this->assertNull($element->fresh()->imagenes);
    }
}
