<?php

namespace Tests\Feature;

use App\Models\Item;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Tests para ItemModal.vue (crear/editar items desde el dashboard).
 * El modal usa la API de ItemController (POST/PUT /items en JSON).
 * Verifica que codigo_barras e imagenes se guardan y cargan correctamente.
 */
class ItemModalTest extends TestCase
{
    use DatabaseTransactions, InventoryTestTrait;

    private $user;
    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createAdminUser();
        $this->category = $this->createCategory('Modal Test Cat', 'component');
    }

    // ──────────────────────────────────────────────
    // Crear item (POST /items) — como hace el modal al guardar
    // ──────────────────────────────────────────────

    public function test_modal_create_element_saves_codigo_barras(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/items', [
                'name'          => 'Tuerca 3mm',
                'type'          => 'element',
                'category_id'   => $this->category->id,
                'unit'          => 'Pieza',
                'min_stock'     => 10,
                'max_stock'     => 500,
                'current_stock' => 100,
                'codigo_barras' => 'TUE-3MM-001',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('items', [
            'name'          => 'Tuerca 3mm',
            'codigo_barras' => 'TUE-3MM-001',
        ]);
    }

    public function test_modal_create_element_saves_imagenes(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/items', [
                'name'          => 'Tornillo M4',
                'type'          => 'element',
                'category_id'   => $this->category->id,
                'unit'          => 'Pieza',
                'min_stock'     => 5,
                'max_stock'     => 200,
                'current_stock' => 50,
                'imagenes'      => 'tornillo m4.webp',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('items', [
            'name'     => 'Tornillo M4',
            'imagenes' => 'tornillo m4.webp',
        ]);
    }

    public function test_modal_create_saves_multiple_imagenes(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/items', [
                'name'          => 'Resistencia SMD',
                'type'          => 'component',
                'category_id'   => $this->category->id,
                'unit'          => 'Pieza',
                'min_stock'     => 100,
                'max_stock'     => 10000,
                'current_stock' => 5000,
                'imagenes'      => 'resistencia smd frente.jpg, resistencia smd atras.jpg, resistencia smd codigo.webp',
            ]);

        $response->assertStatus(201);
        $item = Item::where('name', 'Resistencia SMD')->first();
        $this->assertNotNull($item);
        $this->assertEquals(
            'resistencia smd frente.jpg, resistencia smd atras.jpg, resistencia smd codigo.webp',
            $item->imagenes
        );
    }

    public function test_modal_create_saves_both_codigo_barras_and_imagenes(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/items', [
                'name'          => 'LED Azul 5mm',
                'type'          => 'element',
                'category_id'   => $this->category->id,
                'unit'          => 'Pieza',
                'min_stock'     => 20,
                'max_stock'     => 1000,
                'current_stock' => 200,
                'codigo_barras' => 'LED-BLUE-5MM',
                'imagenes'      => 'led azul 5mm.webp',
                'location'      => 'Cajón B3',
            ]);

        $response->assertStatus(201);

        $item = Item::where('name', 'LED Azul 5mm')->first();
        $this->assertNotNull($item);
        $this->assertEquals('LED-BLUE-5MM', $item->codigo_barras);
        $this->assertEquals('led azul 5mm.webp', $item->imagenes);
        $this->assertEquals('Cajón B3', $item->location);
    }

    public function test_modal_create_works_without_codigo_barras_and_imagenes(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/items', [
                'name'          => 'Item Sin Extras',
                'type'          => 'element',
                'category_id'   => $this->category->id,
                'unit'          => 'Pieza',
                'min_stock'     => 5,
                'max_stock'     => 100,
                'current_stock' => 10,
            ]);

        $response->assertStatus(201);
        $item = Item::where('name', 'Item Sin Extras')->first();
        $this->assertNull($item->codigo_barras);
        $this->assertNull($item->imagenes);
    }

    // ──────────────────────────────────────────────
    // Editar item (PUT /items/{id}) — como hace el modal al actualizar
    // ──────────────────────────────────────────────

    public function test_modal_edit_updates_codigo_barras(): void
    {
        $item = $this->createComponent($this->category, ['codigo_barras' => 'OLD-CODE']);

        $response = $this->actingAs($this->user)
            ->putJson("/items/{$item->id}", [
                'codigo_barras' => 'NEW-CODE-456',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('items', [
            'id'            => $item->id,
            'codigo_barras' => 'NEW-CODE-456',
        ]);
    }

    public function test_modal_edit_updates_imagenes(): void
    {
        $item = $this->createComponent($this->category, ['imagenes' => 'vieja imagen.jpg']);

        $response = $this->actingAs($this->user)
            ->putJson("/items/{$item->id}", [
                'imagenes' => 'nueva foto.webp, otra foto.png',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('items', [
            'id'       => $item->id,
            'imagenes' => 'nueva foto.webp, otra foto.png',
        ]);
    }

    public function test_modal_edit_updates_all_fields_including_new_ones(): void
    {
        $item = $this->createComponent($this->category);

        $response = $this->actingAs($this->user)
            ->putJson("/items/{$item->id}", [
                'name'          => 'Nombre Editado',
                'codigo_barras' => 'EDIT-BARCODE-789',
                'imagenes'      => 'foto editada.webp',
                'location'      => 'Estante Z9',
                'description'   => 'Descripción actualizada',
            ]);

        $response->assertStatus(200);

        $updated = $item->fresh();
        $this->assertEquals('Nombre Editado', $updated->name);
        $this->assertEquals('EDIT-BARCODE-789', $updated->codigo_barras);
        $this->assertEquals('foto editada.webp', $updated->imagenes);
        $this->assertEquals('Estante Z9', $updated->location);
    }

    public function test_modal_edit_can_clear_codigo_barras(): void
    {
        $item = $this->createComponent($this->category, ['codigo_barras' => 'CLEAR-ME']);

        $this->actingAs($this->user)
            ->putJson("/items/{$item->id}", [
                'codigo_barras' => null,
            ]);

        $this->assertDatabaseHas('items', [
            'id'            => $item->id,
            'codigo_barras' => null,
        ]);
    }

    public function test_modal_edit_can_clear_imagenes(): void
    {
        $item = $this->createComponent($this->category, ['imagenes' => 'foto.webp']);

        $this->actingAs($this->user)
            ->putJson("/items/{$item->id}", [
                'imagenes' => '',
            ]);

        $this->assertDatabaseHas('items', [
            'id'       => $item->id,
            'imagenes' => null,
        ]);
    }

    // ──────────────────────────────────────────────
    // La API devuelve los campos nuevos al leer
    // ──────────────────────────────────────────────

    public function test_api_returns_codigo_barras_in_item_list(): void
    {
        $this->createComponent($this->category, [
            'codigo_barras' => 'LIST-CODE-001',
            'imagenes'      => 'lista foto.webp',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/items?type=component');

        $response->assertStatus(200);
        $items = $response->json();
        $item = collect($items)->firstWhere('codigo_barras', 'LIST-CODE-001');
        $this->assertNotNull($item);
        $this->assertEquals('lista foto.webp', $item['imagenes']);
    }

    public function test_api_create_response_includes_codigo_barras_and_imagenes(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/items', [
                'name'          => 'Item Con Respuesta',
                'type'          => 'element',
                'category_id'   => $this->category->id,
                'unit'          => 'Pieza',
                'min_stock'     => 1,
                'max_stock'     => 100,
                'current_stock' => 10,
                'codigo_barras' => 'RESP-001',
                'imagenes'      => 'respuesta.webp',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'codigo_barras' => 'RESP-001',
                'imagenes'      => 'respuesta.webp',
            ]);
    }

    // ──────────────────────────────────────────────
    // Nombre de imagen acepta espacios y extensiones reales
    // ──────────────────────────────────────────────

    public function test_imagenes_accepts_filenames_with_spaces_and_extensions(): void
    {
        $names = [
            'tuerca 3mm.webp',
            'tornillo cabeza plana M4.jpg',
            'resistencia 10k ohm vista frontal.png',
            'led rojo 5mm.webp, led rojo 5mm detalle.jpg',
        ];

        foreach ($names as $name) {
            $response = $this->actingAs($this->user)
                ->postJson('/items', [
                    'name'          => 'Test ' . $name,
                    'type'          => 'element',
                    'category_id'   => $this->category->id,
                    'unit'          => 'Pieza',
                    'min_stock'     => 1,
                    'max_stock'     => 10,
                    'current_stock' => 5,
                    'imagenes'      => $name,
                ]);

            $response->assertStatus(201);
            $this->assertDatabaseHas('items', ['imagenes' => $name]);
        }
    }
}
