<?php

namespace App\Events;

use App\Models\StockAlert;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockAlertEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $stockAlert;
    public $alertType;

    /**
     * Create a new event instance.
     */
    public function __construct(StockAlert $stockAlert, string $alertType = 'new')
    {
        $this->stockAlert = $stockAlert;
        $this->alertType = $alertType;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('stock-alerts'),
            new PrivateChannel('user.notifications'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'stock.alert';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'alert' => [
                'id' => $this->stockAlert->id,
                'type' => $this->alertType,
                'alert_type' => $this->stockAlert->alert_type,
                'message' => $this->stockAlert->message,
                'element' => [
                    'id' => $this->stockAlert->element->id,
                    'name' => $this->stockAlert->element->name,
                    'current_stock' => $this->stockAlert->element->current_stock,
                    'min_stock' => $this->stockAlert->element->min_stock,
                    'category' => $this->stockAlert->element->category,
                ],
                'date' => $this->stockAlert->date->toISOString(),
                'priority' => $this->stockAlert->getPriorityAttribute(),
                'color' => $this->stockAlert->getAlertTypeColorAttribute(),
            ],
            'timestamp' => now()->toISOString(),
        ];
    }
}
