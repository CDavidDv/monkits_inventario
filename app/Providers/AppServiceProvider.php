<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use App\Models\Element;
use App\Observers\ElementObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Forzar URL base solo cuando APP_URL incluye un subdirectorio (ej: .com/inventario)
        $appUrl = config('app.url');
        $path = parse_url($appUrl, PHP_URL_PATH);
        if ($path && $path !== '/') {
            URL::forceRootUrl($appUrl);
        }

        // Registrar observers

        // Directiva @vite compatible con PHP 7.4 (no existe en Laravel 8 nativo)
        // El directive genera PHP que corre en runtime (no en tiempo de compilación de Blade)
        // Esto evita que los hashes de Vite queden cacheados en las vistas compiladas
        Blade::directive('vite', function ($expression) {
            return <<<PHP
<?php
\$_viteFiles = is_array({$expression}) ? {$expression} : [{$expression}];
if (file_exists(public_path('hot'))) {
    \$_viteDevUrl = rtrim(trim(file_get_contents(public_path('hot'))), '/');
    foreach (\$_viteFiles as \$_viteFile) {
        \$_viteExt = pathinfo(\$_viteFile, PATHINFO_EXTENSION);
        if (\$_viteExt === 'js') echo '<script type="module" src="' . \$_viteDevUrl . '/' . \$_viteFile . '"></script>' . PHP_EOL;
        elseif (\$_viteExt === 'css') echo '<link rel="stylesheet" href="' . \$_viteDevUrl . '/' . \$_viteFile . '">' . PHP_EOL;
    }
} elseif (file_exists(public_path('build/manifest.json'))) {
    \$_viteManifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
    foreach (\$_viteFiles as \$_viteFile) {
        if (!isset(\$_viteManifest[\$_viteFile])) continue;
        \$_viteEntry = \$_viteManifest[\$_viteFile];
        \$_viteExt = pathinfo(\$_viteFile, PATHINFO_EXTENSION);
        if (\$_viteExt === 'js') echo '<script type="module" src="' . asset('build/' . \$_viteEntry['file']) . '"></script>' . PHP_EOL;
        elseif (\$_viteExt === 'css') echo '<link rel="stylesheet" href="' . asset('build/' . \$_viteEntry['file']) . '">' . PHP_EOL;
        if (!empty(\$_viteEntry['css'])) {
            foreach (\$_viteEntry['css'] as \$_viteCss) {
                echo '<link rel="stylesheet" href="' . asset('build/' . \$_viteCss) . '">' . PHP_EOL;
            }
        }
    }
}
?>
PHP;
        });
    }
}
