<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearDashboardCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:clear-cache {--user= : ID del usuario específico} {--all : Limpiar cache de todos los usuarios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpiar el cache del dashboard del sistema de inventario';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Limpiando cache del dashboard...');

        if ($this->option('all')) {
            // Limpiar cache de todos los usuarios
            $this->clearAllUserCache();
        } elseif ($userId = $this->option('user')) {
            // Limpiar cache de un usuario específico
            $this->clearUserCache($userId);
        } else {
            // Limpiar cache general del dashboard
            $this->clearGeneralCache();
        }

        $this->info('✅ Cache del dashboard limpiado exitosamente!');
        
        return Command::SUCCESS;
    }

    /**
     * Limpiar cache de todos los usuarios
     */
    private function clearAllUserCache(): void
    {
        $this->info('🗑️ Limpiando cache de todos los usuarios...');
        
        // Buscar todas las claves de cache que empiecen con 'dashboard_data_'
        $keys = Cache::get('dashboard_cache_keys', []);
        
        if (empty($keys)) {
            $this->warn('⚠️ No se encontraron claves de cache para limpiar');
            return;
        }

        $bar = $this->output->createProgressBar(count($keys));
        $bar->start();

        foreach ($keys as $key) {
            Cache::forget($key);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        
        // Limpiar la lista de claves
        Cache::forget('dashboard_cache_keys');
        
        $this->info("🗑️ Cache de " . count($keys) . " usuarios limpiado");
    }

    /**
     * Limpiar cache de un usuario específico
     */
    private function clearUserCache(string $userId): void
    {
        $this->info("👤 Limpiando cache del usuario ID: {$userId}");
        
        $cacheKey = "dashboard_data_{$userId}";
        Cache::forget($cacheKey);
        
        $this->info("✅ Cache del usuario {$userId} limpiado");
    }

    /**
     * Limpiar cache general del dashboard
     */
    private function clearGeneralCache(): void
    {
        $this->info('🌐 Limpiando cache general del dashboard...');
        
        // Limpiar cache relacionado con estadísticas generales
        Cache::forget('dashboard_general_stats');
        Cache::forget('dashboard_category_distribution');
        Cache::forget('dashboard_top_components');
        
        $this->info('✅ Cache general del dashboard limpiado');
    }
}
