<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            self::logAudit('create', $model);
        });

        static::updated(function ($model) {
            self::logAudit('update', $model);
        });

        static::deleted(function ($model) {
            self::logAudit('delete', $model);
        });
    }

    protected static function logAudit(string $action, $model): void
    {
        $user = Auth::user();
        
        AuditLog::create([
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id ?? null,
            'old_values' => $action === 'update' ? $model->getOriginal() : null,
            'new_values' => $action !== 'delete' ? $model->getAttributes() : null,
            'description' => self::getAuditDescription($action, $model),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'user_id' => $user?->id
        ]);
    }

    protected static function getAuditDescription(string $action, $model): string
    {
        $modelName = class_basename($model);
        
        return match($action) {
            'create' => "Se creó {$modelName} '" . ($model->name ?? $model->id) . "'",
            'update' => "Se actualizó {$modelName} '" . ($model->name ?? $model->id) . "'",
            'delete' => "Se eliminó {$modelName} '" . ($model->name ?? $model->id) . "'",
            default => "Acción {$action} en {$modelName}"
        };
    }
}
