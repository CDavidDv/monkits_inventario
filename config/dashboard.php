<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración del Dashboard
    |--------------------------------------------------------------------------
    |
    | Este archivo contiene la configuración para el dashboard del sistema
    | de inventario, incluyendo límites de elementos mostrados y
    | configuraciones de visualización.
    |
    */

    // Límites de elementos mostrados en el dashboard principal
    'limits' => [
        'low_stock_components' => 5,
        'unread_alerts' => 5,
        'recent_movements' => 10,
        'category_distribution' => 6,
        'top_value_components' => 5,
    ],

    // Configuración de alertas
    'alerts' => [
        'auto_refresh_interval' => 30000, // 30 segundos en milisegundos
        'show_critical_first' => true,
        'max_alerts_displayed' => 10,
    ],

    // Configuración de estadísticas
    'stats' => [
        'include_inactive_components' => false,
        'calculate_total_value' => true,
        'show_percentage_changes' => true,
    ],

    // Configuración de movimientos
    'movements' => [
        'show_reference_info' => true,
        'show_user_info' => true,
        'group_by_date' => true,
    ],

    // Configuración de categorías
    'categories' => [
        'show_color_indicators' => true,
        'max_categories_displayed' => 6,
        'sort_by_component_count' => true,
    ],

    // Configuración de componentes
    'components' => [
        'show_stock_percentage' => true,
        'show_cost_information' => true,
        'show_location_info' => false,
        'show_supplier_info' => false,
    ],

    // Configuración de kits
    'kits' => [
        'show_availability_status' => true,
        'show_component_count' => true,
        'show_build_cost' => true,
    ],

    // Configuración de exportación
    'export' => [
        'available_formats' => ['pdf', 'excel', 'csv'],
        'include_charts' => true,
        'include_summaries' => true,
    ],

    // Configuración de notificaciones
    'notifications' => [
        'enable_email_alerts' => false,
        'enable_browser_notifications' => true,
        'sound_enabled' => true,
        'sound_file' => 'videoplayback.mp3',
    ],

    // Configuración de temas
    'theme' => [
        'primary_color' => '#3B82F6',
        'secondary_color' => '#10B981',
        'warning_color' => '#F59E0B',
        'danger_color' => '#EF4444',
        'success_color' => '#10B981',
        'info_color' => '#06B6D4',
    ],

    // Configuración de permisos
    'permissions' => [
        'view_dashboard' => 'view_dashboard',
        'manage_inventory' => 'manage_inventory',
        'view_reports' => 'view_reports',
        'manage_alerts' => 'manage_alerts',
        'export_data' => 'export_data',
    ],

    // Configuración de cache
    'cache' => [
        'enable_cache' => true,
        'cache_ttl' => 300, // 5 minutos
        'cache_prefix' => 'dashboard_',
    ],

    // Configuración de API
    'api' => [
        'enable_real_time_updates' => false,
        'polling_interval' => 5000, // 5 segundos
        'max_requests_per_minute' => 60,
    ],
];
