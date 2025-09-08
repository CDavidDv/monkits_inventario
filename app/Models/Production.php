<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Production extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'kit_id',
        'quantity_requested',
        'quantity_produced',
        'status',
        'start_date',
        'end_date',
        'due_date',
        'components_used',
        'cost',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'due_date' => 'datetime',
        'components_used' => 'array',
        'cost' => 'decimal:2'
    ];

    // Relaciones
    public function kit(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'kit_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    // Métodos auxiliares
    public function getProgressPercentage(): float
    {
        if ($this->quantity_requested == 0) {
            return 0;
        }
        return round(($this->quantity_produced / $this->quantity_requested) * 100, 2);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canBeStarted(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeCompleted(): bool
    {
        return $this->status === 'in_progress' && $this->quantity_produced >= $this->quantity_requested;
    }

    public function getRemainingQuantity(): int
    {
        return max(0, $this->quantity_requested - $this->quantity_produced);
    }

    public function getStatusColorClass(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getStatusText(): string
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'in_progress' => 'En Progreso',
            'completed' => 'Completado',
            'cancelled' => 'Cancelado',
            default => 'Sin Estado'
        };
    }
}
