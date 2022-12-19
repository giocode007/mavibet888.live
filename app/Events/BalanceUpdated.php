<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BalanceUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private string $userId;
    private string $reward;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $userId, string $reward)
    {
        $this->userId = $userId;
        $this->reward = $reward;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('balance');
    }

    public function broadcastAs(){
        return 'player-balance';
    }

    public function broadcastWith(){
        return [
            'userId' => $this->userId,
            'reward' => $this->reward,
        ];
    }
}
