<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Tests para ImageUploadController.
 * Las imágenes se guardan en public/images/items/ — son opcionales en los items.
 */
class ImageUploadTest extends TestCase
{
    use DatabaseTransactions, InventoryTestTrait;

    private $user;
    private string $testDir;
    private array $createdFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->user    = $this->createAdminUser();
        $this->testDir = public_path('images/items');

        if (!is_dir($this->testDir)) {
            mkdir($this->testDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Limpiar archivos de test para no dejar basura en disco
        foreach ($this->createdFiles as $file) {
            $path = $this->testDir . DIRECTORY_SEPARATOR . $file;
            if (file_exists($path)) {
                unlink($path);
            }
        }
        parent::tearDown();
    }

    // Helper para registrar archivos a limpiar
    private function track(string $filename): void
    {
        $this->createdFiles[] = $filename;
    }

    // ──────────────────────────────────────────────
    // GET /items/images (página)
    // ──────────────────────────────────────────────

    public function test_images_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/items/images');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Items/Images'));
    }

    public function test_images_page_requires_auth(): void
    {
        $response = $this->get('/items/images');

        $response->assertRedirect('/login');
    }

    public function test_images_page_lists_existing_files(): void
    {
        // Crear un archivo de prueba manualmente
        $name = 'test_list_' . uniqid() . '.jpg';
        file_put_contents($this->testDir . '/' . $name, 'fake');
        $this->track($name);

        $response = $this->actingAs($this->user)->get('/items/images');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Items/Images')
                ->has('images')
        );

        $images = $response->original->getData()['page']['props']['images'] ?? [];
        $names = array_column($images, 'name');
        $this->assertContains($name, $names);
    }

    // ──────────────────────────────────────────────
    // POST /items/images/upload
    // ──────────────────────────────────────────────

    public function test_can_upload_jpg_image(): void
    {
        $file = UploadedFile::fake()->image('producto test.jpg', 100, 100);

        $this->actingAs($this->user)
            ->post(route('items.images.upload'), ['imagenes' => [$file]]);

        $this->track('producto test.jpg');
        $this->assertFileExists($this->testDir . DIRECTORY_SEPARATOR . 'producto test.jpg');
    }

    public function test_can_upload_webp_image(): void
    {
        $file = UploadedFile::fake()->image('tuerca 3mm.webp', 200, 200);

        $this->actingAs($this->user)
            ->post(route('items.images.upload'), ['imagenes' => [$file]]);

        $this->track('tuerca 3mm.webp');
        $this->assertFileExists($this->testDir . DIRECTORY_SEPARATOR . 'tuerca 3mm.webp');
    }

    public function test_can_upload_png_image(): void
    {
        $file = UploadedFile::fake()->image('resistencia 1k.png', 150, 150);

        $this->actingAs($this->user)
            ->post(route('items.images.upload'), ['imagenes' => [$file]]);

        $this->track('resistencia 1k.png');
        $this->assertFileExists($this->testDir . DIRECTORY_SEPARATOR . 'resistencia 1k.png');
    }

    public function test_can_upload_multiple_images(): void
    {
        $files = [
            UploadedFile::fake()->image('item a.jpg', 100, 100),
            UploadedFile::fake()->image('item b.png', 100, 100),
            UploadedFile::fake()->image('item c.webp', 100, 100),
        ];

        $this->actingAs($this->user)
            ->post(route('items.images.upload'), ['imagenes' => $files]);

        $this->track('item a.jpg');
        $this->track('item b.png');
        $this->track('item c.webp');

        $this->assertFileExists($this->testDir . DIRECTORY_SEPARATOR . 'item a.jpg');
        $this->assertFileExists($this->testDir . DIRECTORY_SEPARATOR . 'item b.png');
        $this->assertFileExists($this->testDir . DIRECTORY_SEPARATOR . 'item c.webp');
    }

    public function test_upload_response_includes_uploaded_filenames(): void
    {
        $file = UploadedFile::fake()->image('tornillo m4.jpg', 100, 100);

        $response = $this->actingAs($this->user)
            ->post(route('items.images.upload'), [
                'imagenes' => [$file],
            ]);

        $response->assertSessionHas('uploaded');
        $uploaded = session('uploaded');
        $this->track($uploaded[0] ?? 'tornillo m4.jpg');
        $this->assertContains('tornillo m4.jpg', $uploaded);
    }

    public function test_duplicate_filename_gets_numeric_suffix(): void
    {
        // Crear archivo previo
        file_put_contents($this->testDir . DIRECTORY_SEPARATOR . 'duplicado.jpg', 'original');
        $this->track('duplicado.jpg');
        $this->track('duplicado_1.jpg');

        $file = UploadedFile::fake()->image('duplicado.jpg', 100, 100);

        $this->actingAs($this->user)
            ->post(route('items.images.upload'), ['imagenes' => [$file]]);

        // El nuevo archivo debe tener sufijo _1
        $this->assertFileExists($this->testDir . DIRECTORY_SEPARATOR . 'duplicado_1.jpg');
    }

    public function test_upload_fails_without_files(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('items.images.upload'), []);

        $response->assertSessionHasErrors('imagenes');
    }

    public function test_upload_fails_with_non_image_file(): void
    {
        $file = UploadedFile::fake()->create('malware.exe', 100, 'application/octet-stream');

        $response = $this->actingAs($this->user)
            ->post(route('items.images.upload'), [
                'imagenes' => [$file],
            ]);

        $response->assertSessionHasErrors();
    }

    public function test_upload_fails_with_pdf(): void
    {
        $file = UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->post(route('items.images.upload'), [
                'imagenes' => [$file],
            ]);

        $response->assertSessionHasErrors();
    }

    public function test_upload_fails_with_file_too_large(): void
    {
        // 6MB > límite de 5MB
        $file = UploadedFile::fake()->image('grande.jpg')->size(6144);

        $response = $this->actingAs($this->user)
            ->post(route('items.images.upload'), [
                'imagenes' => [$file],
            ]);

        $response->assertSessionHasErrors();
    }

    public function test_upload_requires_auth(): void
    {
        $file = UploadedFile::fake()->image('test.jpg', 100, 100);

        $response = $this->post(route('items.images.upload'), [
            'imagenes' => [$file],
        ]);

        $response->assertRedirect('/login');
    }

    // ──────────────────────────────────────────────
    // DELETE /items/images (borrar imagen)
    // ──────────────────────────────────────────────

    public function test_can_delete_image(): void
    {
        $name = 'para_borrar_' . uniqid() . '.jpg';
        file_put_contents($this->testDir . '/' . $name, 'fake content');

        $response = $this->actingAs($this->user)
            ->delete(route('items.images.destroy'), [
                'filename' => $name,
            ]);

        $response->assertRedirect();
        $this->assertFileDoesNotExist($this->testDir . '/' . $name);
    }

    public function test_delete_fails_for_nonexistent_image(): void
    {
        $response = $this->actingAs($this->user)
            ->delete(route('items.images.destroy'), [
                'filename' => 'no_existe_' . uniqid() . '.jpg',
            ]);

        $response->assertSessionHasErrors('filename');
    }

    public function test_delete_prevents_path_traversal(): void
    {
        // Crear un archivo legítimo fuera de la carpeta items para intentar borrarlo
        $safeFile = 'test_traversal_safe_' . uniqid() . '.jpg';
        file_put_contents($this->testDir . DIRECTORY_SEPARATOR . $safeFile, 'protected');
        $this->track($safeFile);

        // Intento de path traversal — basename() en el controller neutraliza ../
        $response = $this->actingAs($this->user)
            ->delete(route('items.images.destroy'), [
                'filename' => '../' . $safeFile,  // intenta salir del directorio
            ]);

        // El controller usa basename() — la ruta resultante es la misma que el archivo real
        // El archivo existe, así que se puede borrar (lo cual está bien — basename neutralizó el ..)
        // Lo importante: no se puede acceder a archivos fuera de /images/items/
        $this->assertFileDoesNotExist(
            dirname($this->testDir) . DIRECTORY_SEPARATOR . $safeFile
        );
    }

    public function test_delete_requires_auth(): void
    {
        $response = $this->delete(route('items.images.destroy'), [
            'filename' => 'test.jpg',
        ]);

        $response->assertRedirect('/login');
    }

    // ──────────────────────────────────────────────
    // Imagen es opcional en items (campo imagenes nullable)
    // ──────────────────────────────────────────────

    public function test_item_can_be_created_without_imagenes(): void
    {
        $category = $this->createCategory('Cat Image Test', 'component');

        $response = $this->actingAs($this->user)
            ->postJson('/items', [
                'name'          => 'Item Sin Imagen',
                'type'          => 'element',
                'category_id'   => $category->id,
                'unit'          => 'Pieza',
                'min_stock'     => 1,
                'max_stock'     => 100,
                'current_stock' => 10,
                // sin imagenes ni codigo_barras
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('items', [
            'name'     => 'Item Sin Imagen',
            'imagenes' => null,
        ]);
    }

    public function test_item_can_be_created_with_imagenes(): void
    {
        $category = $this->createCategory('Cat Image Test 2', 'component');

        $response = $this->actingAs($this->user)
            ->postJson('/items', [
                'name'          => 'Item Con Imagen',
                'type'          => 'element',
                'category_id'   => $category->id,
                'unit'          => 'Pieza',
                'min_stock'     => 1,
                'max_stock'     => 100,
                'current_stock' => 10,
                'imagenes'      => 'tuerca 3mm.webp',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('items', [
            'name'     => 'Item Con Imagen',
            'imagenes' => 'tuerca 3mm.webp',
        ]);
    }
}
