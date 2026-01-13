<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PesertaUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $peserta;

    public function __construct($peserta)
    {
        $this->peserta = $peserta;
    }

    public function broadcastOn()
    {
        return new Channel('ruang-ujian');
    }

    public function broadcastAs()
    {
        return 'PesertaUpdated';
    }

    public function broadcastWith()
    {
        return [
            'peserta' => $this->peserta
        ];
    }
}
