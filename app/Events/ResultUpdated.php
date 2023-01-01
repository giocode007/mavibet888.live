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

    private string $fightId;
    private string $result;
    private string $lastFightNumber;
    private string $fightNumber;
    private string $isCurrentFight;
    private $response = [];


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $fightId, string $result, string $lastFightNumber, string $fightNumber, string $isCurrentFight, $response)
    {
        $this->fightId = $fightId;
        $this->result = $result;
        $this->lastFightNumber = $lastFightNumber;
        $this->fightNumber = $fightNumber;
        $this->isCurrentFight = $isCurrentFight;
        $this->response = $response;
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
            'fightId' => $this->fightId,
            'result' => $this->result,
            'lastFightNumber' => $this->lastFightNumber,
            'fightNumber' => $this->fightNumber,
            'isCurrentFight' => $this->isCurrentFight,
            'response' => $this->response,
        ];
    }
}
