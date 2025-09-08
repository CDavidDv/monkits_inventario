<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'alert_type',
        'message',
        'date',
        'is_read',
        'is_resolved',
        'resolved_at'
    ];

    protected $casts = [
        'date' => 'datetime',
        'is_read' => 'boolean',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime'
    ];

    // Relaciones
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('alert_type', $type);
    }

    public function scopeByItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('date', '>=', now()->subDays($days));
    }

    public function scopeCritical($query)
    {
        return $query->whereIn('alert_type', ['Agotado', 'Crítico']);
    }

    // Métodos auxiliares
    public function markAsRead(): bool
    {
        return $this->update(['is_read' => true]);
    }

    public function markAsResolved(): bool
    {
        return $this->update([
            'is_resolved' => true,
            'resolved_at' => now()
        ]);
    }

    public function getAlertTypeColorAttribute(): string
    {
        return match($this->alert_type) {
            'Agotado' => 'red',
            'Crítico' => 'orange',
            'Mínimo' => 'yellow',
            'Máximo' => 'blue',
            default => 'gray'
        };
    }

    public function getAlertTypeIconAttribute(): string
    {
        return match($this->alert_type) {
            'Agotado' => 'exclamation-triangle',
            'Crítico' => 'exclamation-circle',
            'Mínimo' => 'arrow-down',
            'Máximo' => 'arrow-up',
            default => 'information-circle'
        };
    }

    public function getPriorityAttribute(): int
    {
        return match($this->alert_type) {
            'Agotado' => 1,
            'Crítico' => 2,
            'Mínimo' => 3,
            'Máximo' => 4,
            default => 5
        };
    }

    public static function createAlert(
        int $itemId,
        string $alertType,
        string $message
    ): self {
        // Evitar duplicados recientes (últimas 24 horas)
        $existing = self::where('item_id', $itemId)
            ->where('alert_type', $alertType)
            ->where('is_resolved', false)
            ->where('date', '>=', now()->subDay())
            ->first();

        if ($existing) {
            return $existing;
        }

        return self::create([
            'item_id' => $itemId,
            'alert_type' => $alertType,
            'message' => $message,
            'date' => now(),
            'is_read' => false,
            'is_resolved' => false
        ]);
    }
}
