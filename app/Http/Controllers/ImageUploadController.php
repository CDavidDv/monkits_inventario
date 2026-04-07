<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ImageUploadController extends Controller
{
    // Carpeta dentro de public/images/
    private const ITEMS_DIR = 'items';

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Página de gestión de imágenes.
     */
    public function index()
    {
        $images = $this->getImageList();

        return Inertia::render('Items/Images', [
            'images' => $images,
        ]);
    }

    /**
     * Subir una o varias imágenes.
     */
    public function upload(Request $request)
    {
        if (!$request->hasFile('imagenes')) {
            return back()->withErrors(['imagenes' => 'Selecciona al menos una imagen.']);
        }

        // Normalizar: puede llegar como un solo archivo o como array
        $files = $request->file('imagenes');
        if (!is_array($files)) {
            $files = [$files];
        }

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxBytes = 5 * 1024 * 1024; // 5 MB

        // Validar cada archivo individualmente
        foreach ($files as $file) {
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, $allowed)) {
                return back()->withErrors([
                    'imagenes' => "El archivo '{$file->getClientOriginalName()}' no es válido. Solo se permiten: " . implode(', ', $allowed),
                ]);
            }
            if ($file->getSize() > $maxBytes) {
                return back()->withErrors([
                    'imagenes' => "El archivo '{$file->getClientOriginalName()}' supera los 5MB.",
                ]);
            }
        }

        $dir     = public_path('images/' . self::ITEMS_DIR);
        $uploaded = [];
        $errors   = [];

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        foreach ($files as $file) {
            $original = $file->getClientOriginalName();

            // Sanitizar: solo quitar caracteres peligrosos, conservar espacios y acentos
            $safe = preg_replace('/[\/\\\\:*?"<>|]/', '_', $original);

            // Si ya existe, añadir sufijo numérico
            if (file_exists($dir . DIRECTORY_SEPARATOR . $safe)) {
                $info    = pathinfo($safe);
                $counter = 1;
                do {
                    $safe    = $info['filename'] . "_{$counter}." . ($info['extension'] ?? '');
                    $counter++;
                } while (file_exists($dir . DIRECTORY_SEPARATOR . $safe));
            }

            try {
                $file->move($dir, $safe);
                $uploaded[] = $safe;
            } catch (\Exception $e) {
                $errors[] = "No se pudo guardar: {$original}";
            }
        }

        $message = count($uploaded) . ' imagen(es) subida(s) correctamente.';

        return back()->with([
            'success'        => $message,
            'uploaded'       => $uploaded,
            'upload_errors'  => $errors,   // 'errors' está reservado por Laravel para ViewErrorBag
        ]);
    }

    /**
     * Eliminar una imagen.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'filename' => 'required|string',
        ]);

        // Sanitizar el nombre para evitar path traversal
        $filename = basename($request->filename);
        $path = public_path('images/' . self::ITEMS_DIR . '/' . $filename);

        if (!file_exists($path)) {
            return back()->withErrors(['filename' => 'Imagen no encontrada.']);
        }

        unlink($path);

        return back()->with('success', "Imagen '{$filename}' eliminada.");
    }

    /**
     * Obtiene la lista de imágenes subidas.
     */
    private function getImageList(): array
    {
        $dir = public_path('images/' . self::ITEMS_DIR);

        if (!is_dir($dir)) {
            return [];
        }

        $files = glob($dir . '/*.{jpg,jpeg,png,gif,webp,JPG,JPEG,PNG,GIF,WEBP}', GLOB_BRACE);

        $images = [];
        foreach ($files as $file) {
            $name = basename($file);
            $images[] = [
                'name'     => $name,
                'url'      => asset('images/' . self::ITEMS_DIR . '/' . $name),
                'size'     => filesize($file),
                'modified' => filemtime($file),
            ];
        }

        // Ordenar por más reciente primero
        usort($images, fn($a, $b) => $b['modified'] - $a['modified']);

        return $images;
    }
}
