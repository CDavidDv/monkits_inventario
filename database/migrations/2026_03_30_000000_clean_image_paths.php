<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Limpiar rutas de imágenes - extraer solo nombres de archivo
        $items = DB::table('items')->whereNotNull('imagenes')->get();

        foreach ($items as $item) {
            $imagenes = $item->imagenes;

            // Dividir por comas
            $images = array_map(function($img) {
                $img = trim($img);
                if (empty($img)) {
                    return null;
                }
                // Extraer solo el nombre del archivo (sin rutas)
                return basename($img);
            }, explode(',', $imagenes));

            // Filtrar valores vacíos y rejuntar
            $images = array_filter($images, fn($img) => !empty($img));
            $cleaned = !empty($images) ? implode(', ', $images) : null;

            // Actualizar solo si cambió
            if ($cleaned !== $imagenes) {
                DB::table('items')
                    ->where('id', $item->id)
                    ->update(['imagenes' => $cleaned]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No hay reversión posible para esta limpieza de datos
    }
};
