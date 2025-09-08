<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InventoryAlertService;

class CheckInventoryAlerts extends Command
{
    protected $signature = 'inventory:check-alerts';
    protected $description = 'Verificar alertas de inventario (stock bajo, sobre stock, disponibilidad de kits)';

    public function handle(InventoryAlertService $alertService): int
    {
        $this->info('Verificando alertas de inventario...');

        try {
            // Verificar stock bajo
            $this->info('Verificando stock bajo...');
            $alertService->checkLowStock();

            // Verificar sobre stock
            $this->info('Verificando sobre stock...');
            $alertService->checkOverStock();

            // Verificar disponibilidad de kits
            $this->info('Verificando disponibilidad de kits...');
            $alertService->checkKitAvailability();

            $this->info('Verificación de alertas completada exitosamente.');
            return 0;

        } catch (\Exception $e) {
            $this->error('Error al verificar alertas: ' . $e->getMessage());
            return 1;
        }
    }
}
