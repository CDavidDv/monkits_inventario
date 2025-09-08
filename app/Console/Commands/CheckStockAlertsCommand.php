<?php

namespace App\Console\Commands;

use App\Models\Item;
use App\Models\StockAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckStockAlertsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:check-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica el stock de los items y genera alertas automáticamente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando alertas de stock...');
        
        try {
            // Obtener items activos con valores de stock configurados
            $items = Item::where('active', true)
                ->whereNotNull('min_stock')
                ->whereNotNull('current_stock')
                ->get();

            $alertsCreated = 0;
            
            foreach ($items as $item) {
                $stockPercent = $item->min_stock > 0 
                    ? ($item->current_stock / $item->min_stock) * 100 
                    : 0;

                // Determinar el tipo de alerta
                $alertType = null;
                $message = null;

                if ($item->current_stock == 0) {
                    $alertType = 'Agotado';
                    $message = "El item '{$item->name}' está agotado";
                } elseif ($item->current_stock <= ($item->min_stock * 0.25)) {
                    $alertType = 'Crítico';
                    $message = "El item '{$item->name}' tiene stock crítico ({$item->current_stock} unidades)";
                } elseif ($item->current_stock <= $item->min_stock) {
                    $alertType = 'Mínimo';
                    $message = "El item '{$item->name}' ha alcanzado el stock mínimo ({$item->current_stock}/{$item->min_stock})";
                } elseif (isset($item->max_stock) && $item->current_stock >= $item->max_stock) {
                    $alertType = 'Máximo';
                    $message = "El item '{$item->name}' ha superado el stock máximo ({$item->current_stock}/{$item->max_stock})";
                }

                // Crear alerta si corresponde
                if ($alertType && $message) {
                    $alert = StockAlert::createAlert($item->id, $alertType, $message);
                    
                    if ($alert->wasRecentlyCreated) {
                        $alertsCreated++;
                        $this->line("✓ Alerta creada para {$item->name}: {$alertType}");
                    }
                }
            }

            // Marcar alertas obsoletas como resueltas
            $resolvedAlerts = $this->resolveObsoleteAlerts();

            $this->info("Verificación completada:");
            $this->info("- {$alertsCreated} alertas nuevas creadas");
            $this->info("- {$resolvedAlerts} alertas obsoletas resueltas");
            $this->info("- {$items->count()} items verificados");

            Log::info("Verificación de alertas de stock completada", [
                'alerts_created' => $alertsCreated,
                'alerts_resolved' => $resolvedAlerts,
                'items_checked' => $items->count()
            ]);

        } catch (\Exception $e) {
            $this->error("Error verificando alertas de stock: " . $e->getMessage());
            Log::error("Error en verificación de alertas de stock", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Resolver alertas que ya no son aplicables
     */
    private function resolveObsoleteAlerts(): int
    {
        $resolved = 0;

        // Obtener alertas activas
        $activeAlerts = StockAlert::unresolved()->with('item')->get();

        foreach ($activeAlerts as $alert) {
            if (!$alert->item || !$alert->item->active) {
                // Item eliminado o inactivo
                $alert->markAsResolved();
                $resolved++;
                continue;
            }

            $item = $alert->item;
            $shouldResolve = false;

            switch ($alert->alert_type) {
                case 'Agotado':
                    $shouldResolve = $item->current_stock > 0;
                    break;
                case 'Crítico':
                    $shouldResolve = $item->current_stock > ($item->min_stock * 0.25);
                    break;
                case 'Mínimo':
                    $shouldResolve = $item->current_stock > $item->min_stock;
                    break;
                case 'Máximo':
                    $shouldResolve = !isset($item->max_stock) || $item->current_stock < $item->max_stock;
                    break;
            }

            if ($shouldResolve) {
                $alert->markAsResolved();
                $resolved++;
                $this->line("✓ Alerta resuelta para {$item->name}: {$alert->alert_type}");
            }
        }

        return $resolved;
    }
}
