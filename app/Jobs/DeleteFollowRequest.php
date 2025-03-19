<?php

namespace App\Jobs;

use App\Models\Follow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteFollowRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $followId;

    /**
     * Créer une nouvelle instance du Job.
     */
    public function __construct($followId)
    {
        $this->followId = $followId;
    }

    /**
     * Exécuter le Job pour supprimer la demande après 20 jours.
     */
    public function handle()
    {
        $follow = Follow::find($this->followId);
        
        // Vérifier si la demande existe toujours et si elle est acceptée/rejetée
        if ($follow && in_array($follow->status, ['accepted', 'rejected'])) {
            $follow->delete();
        }
    }
}
