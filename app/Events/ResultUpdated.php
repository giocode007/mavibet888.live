<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResultUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private string $result;
    private string $fightNumber;
    private string $isCurrentFight;


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $result, string $fightNumber, string $isCurrentFight)
    {
        $this->result = $result;
        $this->fightNumber = $fightNumber;
        $this->isCurrentFight = $isCurrentFight;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('result');
    }

    public function broadcastAs(){
        return 'player-result';
    }

    public function broadcastWith(){
        return [
            'result' => $this->result,
            'fightNumber' => $this->fightNumber,
            'isCurrentFight' => $this->isCurrentFight,
        ];
    }
}
