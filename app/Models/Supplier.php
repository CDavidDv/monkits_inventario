<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'supplier';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'website',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    // Métodos auxiliares
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function activate()
    {
        $this->update(['status' => 'active']);
    }

    public function deactivate()
    {
        $this->update(['status' => 'inactive']);
    }

    public function toggleStatus()
    {
        $newStatus = $this->status === 'active' ? 'inactive' : 'active';
        $this->update(['status' => $newStatus]);
    }
}