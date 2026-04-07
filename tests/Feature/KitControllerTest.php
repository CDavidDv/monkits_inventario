<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\ItemItems;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Tests para KitController (CRUD de kits).
 * Verifica: crear kits con componentes, código de barras e imágenes.
 */
class KitControllerTest extends TestCase
{
    use DatabaseTransactions, InventoryTestTrait;

    private $user;
    private $category;
    private $component1;
    private $component2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createAdminUser();
        $this->category = $this->createCategory('Kits Test', 'kit');

        $compCat = $this->createCategory('Componentes Test', 'component');
        $this->component1 = $this->createComponent($compCat, [
            'name'          => 'Resistencia para Kit',
            'current_stock' => 500,
            'purchase_cost' => 0.50,
        ]);
        $this->component2 = $this->createComponent($compCat, [
            'name'          => 'LED para Kit',
            'current_stock' => 300,
            'purchase_cost' => 0.20,
        ]);
    }

    // ──────────────────────────────────────────────
    // GET /inventory-kits/create
    // ──────────────────────────────────────────────

    public function test_create_kit_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/inventory-kits/create');

        $response->assertStatus(200);
    }

    public function test_create_kit_page_requires_auth(): void
    {
        $response = $this->get('/inventory-kits/create');

        $response->assertRedirect('/login');
    }

    // ──────────────────────────────────────────────
    // POST /inventory-kits (store)
    // ──────────────────────────────────────────────

    public function test_can_create_kit_with_components(): void
    {
        $response = $this->actingAs($this->user)->post('/inventory-kits', [
            'name'        => 'Kit Básico de Prueba',
            'description' => 'Kit para tests',
            'category_id' => $this->category->id,
            'unit'        => 'Pieza',
            'min_stock'   => 1,
            'max_stock'   => 50,
            'components'  => [
                ['component_id' => $this->component1->id, 'quantity' => 2],
                ['component_id' => $this->component2->id, 'quantity' => 5],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('items', [
            'name' => 'Kit Básico de Prueba',
            'type' => 'kit',
        ]);
    }

    public function test_can_create_kit_with_codigo_barras(): void
    {
        $response = $this->actingAs($this->user)->post('/inventory-kits', [
            'name'           => 'Kit con Barcode',
            'category_id'    => $this->category->id,
            'unit'           => 'Pieza',
            'min_stock'      => 1,
            'max_stock'      => 20,
            'codigo_barras'  => 'KIT-001-BARCODE',
            'components'     => [
                ['component_id' => $this->component1->id, 'quantity' => 3],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('items', [
            'name'          => 'Kit con Barcode',
            'type'          => 'kit',
            'codigo_barras' => 'KIT-001-BARCODE',
        ]);
    }

    public function test_can_create_kit_with_imagenes(): void
    {
        $response = $this->actingAs($this->user)->post('/inventory-kits', [
            'name'        => 'Kit con Imágenes',
            'category_id' => $this->category->id,
            'unit'        => 'Pieza',
            'min_stock'   => 1,
            'max_stock'   => 20,
            'imagenes'    => 'kit-front.jpg, kit-back.png, kit-componentes.jpg',
            'components'  => [
                ['component_id' => $this->component1->id, 'quantity' => 1],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('items', [
            'name'     => 'Kit con Imágenes',
            'imagenes' => 'kit-front.jpg, kit-back.png, kit-componentes.jpg',
        ]);
    }

    public function test_kit_stores_component_relationships(): void
    {
        $this->actingAs($this->user)->post('/inventory-kits', [
            'name'        => 'Kit Relaciones',
            'category_id' => $this->category->id,
            'unit'        => 'Pieza',
            'min_stock'   => 1,
            'max_stock'   => 10,
            'components'  => [
                ['component_id' => $this->component1->id, 'quantity' => 4],
                ['component_id' => $this->component2->id, 'quantity' => 8],
            ],
        ]);

        $kit = Item::where('name', 'Kit Relaciones')->first();
        $this->assertNotNull($kit);
        $kit->load('kitComponents');
        $this->assertCount(2, $kit->kitComponents);
    }

    public function test_kit_calculates_purchase_cost_from_components(): void
    {
        $this->actingAs($this->user)->post('/inventory-kits', [
            'name'        => 'Kit Costo',
            'category_id' => $this->category->id,
            'unit'        => 'Pieza',
            'min_stock'   => 1,
            'max_stock'   => 10,
            'components'  => [
                ['component_id' => $this->component1->id, 'quantity' => 2], // 2 * 0.50 = 1.00
                ['component_id' => $this->component2->id, 'quantity' => 5], // 5 * 0.20 = 1.00
            ],
        ]);

        $kit = Item::where('name', 'Kit Costo')->first();
        $this->assertEquals(2.00, (float) $kit->purchase_cost);
    }

    public function test_store_kit_fails_without_components(): void
    {
        $response = $this->actingAs($this->user)->post('/inventory-kits', [
            'name'        => 'Kit Sin Componentes',
            'category_id' => $this->category->id,
            'unit'        => 'Pieza',
            'min_stock'   => 1,
            'max_stock'   => 10,
            'components'  => [],
        ]);

        $response->assertSessionHasErrors('components');
    }

    public function test_store_kit_fails_without_name(): void
    {
        $response = $this->actingAs($this->user)->post('/inventory-kits', [
            'name'        => '',
            'category_id' => $this->category->id,
            'unit'        => 'Pieza',
            'min_stock'   => 1,
            'max_stock'   => 10,
            'components'  => [
                ['component_id' => $this->component1->id, 'quantity' => 1],
            ],
        ]);

        $response->assertSessionHasErrors('name');
    }

    // ──────────────────────────────────────────────
    // GET /inventory-kits/{id} (show)
    // ──────────────────────────────────────────────

    public function test_can_view_kit_detail(): void
    {
        $kit = $this->createKit($this->category, [
            'codigo_barras' => 'KIT-VIEW-001',
            'imagenes'      => 'kit.jpg',
        ]);

        $response = $this->actingAs($this->user)->get("/inventory-kits/{$kit->id}");

        $response->assertStatus(200);
    }

    // ──────────────────────────────────────────────
    // GET /inventory-kits/{id}/edit
    // ──────────────────────────────────────────────

    public function test_edit_kit_page_loads(): void
    {
        $kit = $this->createKit($this->category);

        $response = $this->actingAs($this->user)->get("/inventory-kits/{$kit->id}/edit");

        $response->assertStatus(200);
    }

    // ──────────────────────────────────────────────
    // PUT /inventory-kits/{id} (update)
    // ──────────────────────────────────────────────

    public function test_can_update_kit_codigo_barras(): void
    {
        $kit = $this->createKit($this->category);

        $response = $this->actingAs($this->user)->put("/inventory-kits/{$kit->id}", [
            'name'           => $kit->name,
            'category_id'    => $this->category->id,
            'unit'           => $kit->unit,
            'min_stock'      => $kit->min_stock,
            'max_stock'      => $kit->max_stock,
            'codigo_barras'  => 'KIT-UPDATED-BARCODE',
            'components'     => [
                ['component_id' => $this->component1->id, 'quantity' => 2],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('items', [
            'id'            => $kit->id,
            'codigo_barras' => 'KIT-UPDATED-BARCODE',
        ]);
    }

    public function test_can_update_kit_imagenes(): void
    {
        $kit = $this->createKit($this->category, ['imagenes' => 'old.jpg']);

        $this->actingAs($this->user)->put("/inventory-kits/{$kit->id}", [
            'name'        => $kit->name,
            'category_id' => $this->category->id,
            'unit'        => $kit->unit,
            'min_stock'   => $kit->min_stock,
            'max_stock'   => $kit->max_stock,
            'imagenes'    => 'new1.jpg, new2.jpg',
            'components'  => [
                ['component_id' => $this->component1->id, 'quantity' => 1],
            ],
        ]);

        $this->assertDatabaseHas('items', [
            'id'       => $kit->id,
            'imagenes' => 'new1.jpg, new2.jpg',
        ]);
    }

    // ──────────────────────────────────────────────
    // GET /inventory-kits (index)
    // ──────────────────────────────────────────────

    public function test_kit_index_loads(): void
    {
        $this->createKit($this->category);

        $response = $this->actingAs($this->user)->get('/inventory-kits');

        $response->assertStatus(200);
    }
}
