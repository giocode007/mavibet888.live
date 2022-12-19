<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BetUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private string $userId;
    private string $allMeronBet;
    private string $allWalaBet;
    private string $allDrawBet;
    private string $meronPayout;
    private string $walaPayout;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $userId, string $allMeronBet, string $allWalaBet, string $allDrawBet, string $meronPayout, string $walaPayout,)
    {
        $this->userId = $userId;
        $this->allMeronBet = $allMeronBet;
        $this->allWalaBet = $allWalaBet;
        $this->allDrawBet = $allDrawBet;
        $this->meronPayout = $meronPayout;
        $this->walaPayout = $walaPayout;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('bettings');
    }

    public function broadcastAs(){
        return 'player-bet';
    }

    public function broadcastWith(){
        return [
            'userId' => $this->userId,
            'allMeronBet' => $this->allMeronBet,
            'allWalaBet' => $this->allWalaBet,
            'allDrawBet' => $this->allDrawBet,
            'meronPayout' => $this->meronPayout,
            'walaPayout' => $this->walaPayout,
        ];
    }

}
