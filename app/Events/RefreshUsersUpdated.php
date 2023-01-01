<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RefreshUsersUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private string $isReload;
    private string $userId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $isReload,string $userId)
    {
        $this->isReload = $isReload;
        $this->userId = $userId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('refresh');
    }

    public function broadcastAs(){
        return 'player-refresh';
    }

    public function broadcastWith(){
        return [
            'isReload' => $this->isReload,
            'userId' => $this->userId,
        ];
    }
    
}
