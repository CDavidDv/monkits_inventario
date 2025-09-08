<?php

namespace App\Http\Middleware;

use App\Models\SystemLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LogSystemActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only log for authenticated users
        if (Auth::check()) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    /**
     * Log the system activity
     */
    private function logActivity(Request $request, $response)
    {
        // Skip logging for certain routes
        if ($this->shouldSkipLogging($request)) {
            return;
        }

        $method = $request->method();
        $route = $request->route();
        $routeName = $route ? $route->getName() : null;
        $action = $this->determineAction($method, $routeName);
        $module = $this->determineModule($routeName, $request->path());

        // Only log significant actions
        if ($action && $module) {
            $description = $this->generateDescription($action, $module, $request);
            
            SystemLog::logAction(
                $action,
                $module,
                $description,
                null, // loggable object - would need to be determined based on context
                null, // old values - would need model events for this
                null  // new values - would need model events for this
            );
        }
    }

    /**
     * Determine if we should skip logging for this request
     */
    private function shouldSkipLogging(Request $request): bool
    {
        $routeName = $request->route() ? $request->route()->getName() : '';
        $path = $request->path();

        // Skip these routes/paths
        $skipRoutes = [
            'api.inventory.*',
            '*.dashboard',
            'logout',
            'profile.show',
        ];

        $skipPaths = [
            'api/',
            'build/',
            'storage/',
            'vendor/',
        ];

        // Skip API calls and asset requests
        foreach ($skipPaths as $skipPath) {
            if (Str::startsWith($path, $skipPath)) {
                return true;
            }
        }

        // Skip certain route patterns
        foreach ($skipRoutes as $pattern) {
            if (Str::is($pattern, $routeName)) {
                return true;
            }
        }

        // Skip GET requests to index/show pages (viewing) - Don't log view actions
        if ($request->method() === 'GET' && in_array($request->method(), ['GET'])) {
            if (Str::endsWith($routeName, ['.index', '.show', '.edit', '.create'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine the action type based on HTTP method and route
     */
    private function determineAction(string $method, ?string $routeName): ?string
    {
        if (!$routeName) {
            return null;
        }

        // Handle auth routes
        if (Str::contains($routeName, 'login')) {
            return 'login';
        }
        
        if (Str::contains($routeName, 'logout')) {
            return 'logout';
        }

        // Handle CRUD operations
        switch ($method) {
            case 'POST':
                if (Str::endsWith($routeName, '.store')) {
                    return 'create';
                }
                if (Str::contains($routeName, ['export', 'cleanup', 'toggle', 'adjust', 'assign'])) {
                    return 'update';
                }
                return 'create';

            case 'PUT':
            case 'PATCH':
                return 'update';

            case 'DELETE':
                return 'delete';

            // case 'GET':
            //    if (Str::endsWith($routeName, ['.show', '.edit', '.index'])) {
            //        return 'view';
            //    }
            //    if (Str::contains($routeName, 'export')) {
            //        return 'export';
            //   }
            //    return 'view';
            

            default:
                return null;
        }
    }

    /**
     * Determine the module based on route name and path
     */
    private function determineModule(?string $routeName, string $path): ?string
    {
        if (!$routeName) {
            return null;
        }

        // Map route prefixes to modules
        $moduleMap = [
            'items.' => 'item',
            'inventory.' => 'inventory',
            'inventario.' => 'inventory',
            'categories.' => 'category',
            'suppliers.' => 'supplier',
            'production.' => 'production',
            'system-logs.' => 'system',
            'inventory-movements.' => 'inventory',
            'admin.' => 'admin',
            'worker.' => 'inventory',
            'supervisor.' => 'system',
            'elements.' => 'inventory',
            'login' => 'auth',
            'logout' => 'auth',
            'register' => 'auth',
        ];

        foreach ($moduleMap as $prefix => $module) {
            if (Str::startsWith($routeName, $prefix) || Str::contains($routeName, $prefix)) {
                return $module;
            }
        }

        // Fallback based on path
        if (Str::contains($path, 'item')) return 'item';
        if (Str::contains($path, 'inventor')) return 'inventory';
        if (Str::contains($path, 'production')) return 'production';
        if (Str::contains($path, 'supplier')) return 'supplier';
        if (Str::contains($path, 'category')) return 'category';
        if (Str::contains($path, 'admin')) return 'admin';
        if (Str::contains($path, 'system')) return 'system';
        if (Str::contains($path, 'audit')) return 'system';

        return 'system';
    }

    /**
     * Generate a human-readable description of the action
     */
    private function generateDescription(string $action, string $module, Request $request): string
    {
        $user = Auth::user();
        $userName = $user ? $user->name : 'Usuario';
        $routeName = $request->route() ? $request->route()->getName() : '';

        // Create action descriptions
        $actionDescriptions = [
            'create' => 'creó',
            'update' => 'actualizó',
            'delete' => 'eliminó',
            'view' => 'consultó',
            'export' => 'exportó',
            'login' => 'inició sesión',
            'logout' => 'cerró sesión',
        ];

        $moduleDescriptions = [
            'item' => 'un item',
            'inventory' => 'el inventario',
            'category' => 'una categoría',
            'supplier' => 'un proveedor',
            'production' => 'la producción',
            'system' => 'el sistema',
            'admin' => 'la administración',
            'auth' => 'autenticación',
        ];

        $actionText = $actionDescriptions[$action] ?? $action;
        $moduleText = $moduleDescriptions[$module] ?? $module;

        // Special cases for specific routes
        if (Str::contains($routeName, 'export')) {
            return "{$userName} exportó datos de {$moduleText}";
        }
        
        if (Str::contains($routeName, 'cleanup')) {
            return "{$userName} realizó limpieza de registros del sistema";
        }
        
        if (Str::contains($routeName, 'adjust-stock')) {
            return "{$userName} ajustó el stock de un item";
        }
        
        if (Str::contains($routeName, 'toggle')) {
            return "{$userName} cambió el estado de {$moduleText}";
        }

        if ($action === 'login') {
            return "{$userName} inició sesión en el sistema";
        }
        
        if ($action === 'logout') {
            return "{$userName} cerró sesión";
        }

        return "{$userName} {$actionText} {$moduleText}";
    }
}