<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FightUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private string $isOpen;


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $isOpen)
    {
        $this->isOpen = $isOpen;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('fights');
    }

    public function broadcastAs(){
        return 'player-fight';
    }

    public function broadcastWith(){
        return [
            'isOpen' => $this->isOpen,
        ];
    }
}
