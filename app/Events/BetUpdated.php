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
    private string $userName;
    private string $betOn;
    private string $amount;
    private string $roleType;
    private string $allMeronBet;
    private string $allWalaBet;
    private string $allDrawBet;
    private string $meronPayout;
    private string $walaPayout;
    private string $allRealMeronBet;
    private string $allRealWalaBet;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $userId, string $userName, string $betOn, string $amount, string $roleType, string $allMeronBet, 
    string $allWalaBet, string $allDrawBet, string $meronPayout, string $walaPayout, 
    string $allRealMeronBet, string $allRealWalaBet)
    {
        $this->userId = $userId;
        $this->userName = $userName;
        $this->betOn = $betOn;
        $this->amount = $amount;
        $this->roleType = $roleType;
        $this->allMeronBet = $allMeronBet;
        $this->allWalaBet = $allWalaBet;
        $this->allDrawBet = $allDrawBet;
        $this->meronPayout = $meronPayout;
        $this->walaPayout = $walaPayout;
        $this->allRealMeronBet = $allRealMeronBet;
        $this->allRealWalaBet = $allRealWalaBet;
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
            'userName' => $this->userName,
            'betOn' => $this->betOn,
            'amount' => $this->amount,
            'roleType' => $this->roleType,
            'allMeronBet' => $this->allMeronBet,
            'allWalaBet' => $this->allWalaBet,
            'allDrawBet' => $this->allDrawBet,
            'meronPayout' => $this->meronPayout,
            'walaPayout' => $this->walaPayout,
            'allRealMeronBet' => $this->allRealMeronBet,
            'allRealWalaBet' => $this->allRealWalaBet,
        ];
    }

}
