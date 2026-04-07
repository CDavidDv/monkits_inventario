<?php

namespace Tests\Feature;

use App\Models\Item;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Tests para ImportController (importación masiva CSV).
 * Verifica: página de importación, descarga de plantilla, importar CSV válido e inválido.
 */
class ImportItemsTest extends TestCase
{
    use DatabaseTransactions, InventoryTestTrait;

    private $user;
    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createAdminUser();
        $this->category = $this->createCategory('Import Test Cat', 'component');
    }

    // ──────────────────────────────────────────────
    // GET /items/import
    // ──────────────────────────────────────────────

    public function test_import_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/items/import');

        $response->assertStatus(200);
    }

    public function test_import_page_requires_auth(): void
    {
        $response = $this->get('/items/import');

        $response->assertRedirect('/login');
    }

    public function test_import_page_shows_categories(): void
    {
        $response = $this->actingAs($this->user)->get('/items/import');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Items/Import')
                ->has('categories')
        );
    }

    // ──────────────────────────────────────────────
    // GET /items/import/template
    // ──────────────────────────────────────────────

    public function test_template_download_returns_csv(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/items/import/template');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="plantilla_items.csv"');
    }

    public function test_template_contains_required_columns(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/items/import/template');

        $content = $response->getContent();
        $this->assertStringContainsString('nombre', $content);
        $this->assertStringContainsString('tipo', $content);
        $this->assertStringContainsString('category_id', $content);
        $this->assertStringContainsString('unidad', $content);
        $this->assertStringContainsString('stock_minimo', $content);
        $this->assertStringContainsString('stock_maximo', $content);
        $this->assertStringContainsString('codigo_barras', $content);
        $this->assertStringContainsString('imagenes', $content);
    }

    public function test_template_requires_auth(): void
    {
        $response = $this->get('/items/import/template');

        $response->assertRedirect('/login');
    }

    // ──────────────────────────────────────────────
    // POST /items/import (import)
    // ──────────────────────────────────────────────

    public function test_can_import_valid_csv(): void
    {
        $csvContent = implode("\n", [
            'nombre,tipo,category_id,unidad,stock_minimo,stock_maximo,stock_actual,costo_compra,precio_venta,ubicacion,numero_serie,codigo_barras,imagenes,descripcion',
            "Resistencia Import,component,{$this->category->id},Pieza,10,500,100,0.50,1.00,Estante A,SN-001,IMP-001,res.jpg,Resistencia importada",
            "LED Import,component,{$this->category->id},Pieza,5,1000,200,0.20,0.50,Estante B,,IMP-002,led.jpg led2.jpg,LED importado",
        ]);

        $file = UploadedFile::fake()->createWithContent('items.csv', $csvContent);

        $response = $this->actingAs($this->user)
            ->post('/items/import', ['archivo' => $file]);

        $response->assertRedirect();
        $response->assertSessionHas('imported_count', 2);

        $this->assertDatabaseHas('items', [
            'name'          => 'Resistencia Import',
            'codigo_barras' => 'IMP-001',
            'imagenes'      => 'res.jpg',
        ]);
        $this->assertDatabaseHas('items', [
            'name'          => 'LED Import',
            'codigo_barras' => 'IMP-002',
        ]);
    }

    public function test_import_respects_stock_values(): void
    {
        $csvContent = implode("\n", [
            'nombre,tipo,category_id,unidad,stock_minimo,stock_maximo,stock_actual,costo_compra,precio_venta,ubicacion,numero_serie,codigo_barras,imagenes,descripcion',
            "Item Stock Test,element,{$this->category->id},Pieza,25,400,150,0.75,1.50,,,,,",
        ]);

        $file = UploadedFile::fake()->createWithContent('items.csv', $csvContent);

        $this->actingAs($this->user)->post('/items/import', ['archivo' => $file]);

        $item = Item::where('name', 'Item Stock Test')->first();
        $this->assertNotNull($item);
        $this->assertEquals(25, (float) $item->min_stock);
        $this->assertEquals(400, (float) $item->max_stock);
        $this->assertEquals(150, (float) $item->current_stock);
    }

    public function test_import_fails_without_file(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/items/import', []);

        $response->assertSessionHasErrors('archivo');
    }

    public function test_import_fails_with_wrong_file_type(): void
    {
        $file = UploadedFile::fake()->create('items.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->actingAs($this->user)
            ->post('/items/import', ['archivo' => $file]);

        $response->assertSessionHasErrors('archivo');
    }

    public function test_import_reports_invalid_rows_but_imports_valid_ones(): void
    {
        $csvContent = implode("\n", [
            'nombre,tipo,category_id,unidad,stock_minimo,stock_maximo,stock_actual,costo_compra,precio_venta,ubicacion,numero_serie,codigo_barras,imagenes,descripcion',
            "Válido Import,component,{$this->category->id},Pieza,5,100,20,,,,,VALID-001,,",
            "Tipo Inválido,invalido,{$this->category->id},Pieza,5,100,20,,,,,,,",  // tipo inválido
            "Sin Nombre,,{$this->category->id},Pieza,5,100,20,,,,,,,",              // sin nombre
        ]);

        $file = UploadedFile::fake()->createWithContent('items.csv', $csvContent);

        $response = $this->actingAs($this->user)
            ->post('/items/import', ['archivo' => $file]);

        $response->assertRedirect();
        $response->assertSessionHas('imported_count', 1);

        $this->assertDatabaseHas('items', ['name' => 'Válido Import', 'codigo_barras' => 'VALID-001']);
        $this->assertDatabaseMissing('items', ['name' => 'Tipo Inválido']);
    }

    public function test_import_skips_empty_rows(): void
    {
        $csvContent = implode("\n", [
            'nombre,tipo,category_id,unidad,stock_minimo,stock_maximo,stock_actual,costo_compra,precio_venta,ubicacion,numero_serie,codigo_barras,imagenes,descripcion',
            "Item Real,component,{$this->category->id},Pieza,5,100,20,,,,,SKIP-001,,",
            ",,,,,,,,,,,,,",  // fila vacía
            ",,,,,,,,,,,,,",  // fila vacía
        ]);

        $file = UploadedFile::fake()->createWithContent('items.csv', $csvContent);

        $response = $this->actingAs($this->user)
            ->post('/items/import', ['archivo' => $file]);

        $response->assertSessionHas('imported_count', 1);
    }

    public function test_import_fails_with_missing_required_columns(): void
    {
        $csvContent = implode("\n", [
            'nombre,descripcion',  // faltan columnas requeridas
            'Item Sin Cols,Descripción',
        ]);

        $file = UploadedFile::fake()->createWithContent('items.csv', $csvContent);

        $response = $this->actingAs($this->user)
            ->post('/items/import', ['archivo' => $file]);

        $response->assertSessionHasErrors('archivo');
    }

    public function test_import_requires_auth(): void
    {
        $file = UploadedFile::fake()->createWithContent('items.csv', 'nombre,tipo');

        $response = $this->post('/items/import', ['archivo' => $file]);

        $response->assertRedirect('/login');
    }

    public function test_import_with_all_three_item_types(): void
    {
        $kitCat = $this->createCategory('Kit Import Cat', 'kit');
        $elemCat = $this->createCategory('Element Import Cat', 'element');

        $csvContent = implode("\n", [
            'nombre,tipo,category_id,unidad,stock_minimo,stock_maximo,stock_actual,costo_compra,precio_venta,ubicacion,numero_serie,codigo_barras,imagenes,descripcion',
            "Elemento Importado,element,{$elemCat->id},Pieza,5,100,20,,,,,,",
            "Componente Importado,component,{$this->category->id},Pieza,10,200,50,,,,,,",
            "Kit Importado,kit,{$kitCat->id},Pieza,1,20,0,,,,,,",
        ]);

        $file = UploadedFile::fake()->createWithContent('items.csv', $csvContent);

        $response = $this->actingAs($this->user)
            ->post('/items/import', ['archivo' => $file]);

        $response->assertSessionHas('imported_count', 3);

        $this->assertDatabaseHas('items', ['name' => 'Elemento Importado', 'type' => 'element']);
        $this->assertDatabaseHas('items', ['name' => 'Componente Importado', 'type' => 'component']);
        $this->assertDatabaseHas('items', ['name' => 'Kit Importado', 'type' => 'kit']);
    }
}
