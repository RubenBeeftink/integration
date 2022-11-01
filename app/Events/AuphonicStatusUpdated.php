<?php

namespace App\Events;

use App\Models\Episode;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuphonicStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Episode
     */
    protected Episode $episode;

    public function __construct(Episode $episode)
    {
        $this->episode = $episode;
        $this->queue = 'notifications';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return PrivateChannel
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('auphonicStatus.'.$this->episode->id);
    }

    /**
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'auphonic.status.updated';
    }

    /**
     * @return array
     */
    public function broadcastWith(): array
    {
        return ['episode' => $this->episode];
    }
}
