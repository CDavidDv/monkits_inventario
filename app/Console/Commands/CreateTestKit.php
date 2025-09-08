<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Item;
use App\Models\ItemItems;

class CreateTestKit extends Command
{
    protected $signature = 'kit:create-test';
    protected $description = 'Crear un kit de prueba con componentes';

    public function handle()
    {
        // Crear un kit
        $kit = Item::create([
            'name' => 'Kit de Prueba',
            'description' => 'Kit de prueba con componentes para testing',
            'type' => 'kit',
            'category_id' => 1, // Asumiendo que existe la categoría con ID 1
            'unit' => 'Kit',
            'min_stock' => 1,
            'max_stock' => 10,
            'current_stock' => 0,
            'purchase_cost' => 0, // Se calculará basado en los componentes
            'active' => true
        ]);

        // Obtener algunos items para usar como componentes
        $components = Item::where('type', '!=', 'kit')
            ->where('active', true)
            ->take(3)
            ->get();
        
        if ($components->count() === 0) {
            $this->error('No hay componentes disponibles. Ejecuta primero: php artisan db:seed --class=ItemsSeeder');
            return;
        }

        $totalCost = 0;
        foreach($components as $component) {
            ItemItems::create([
                'item_id' => $kit->id,
                'item_id_2' => $component->id,
                'quantity' => 2
            ]);
            $totalCost += ($component->purchase_cost ?? 0) * 2;
        }

        // Actualizar el costo del kit
        $kit->update(['purchase_cost' => $totalCost]);

        $this->info("Kit creado con ID: {$kit->id} y {$kit->kitComponents()->count()} componentes");
        $this->info("Nombre: {$kit->name}");
        $this->info("Costo total: $" . number_format($totalCost, 2));
        
        foreach($kit->kitComponents as $kitComponent) {
            $component = $kitComponent->component;
            $this->line("- {$component->name}: {$kitComponent->quantity} unidades ($" . number_format($component->purchase_cost ?? 0, 2) . " c/u)");
        }
    }
}