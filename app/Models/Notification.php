<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Override;

class Notification extends DatabaseNotification
{
    //
    use MassPrunable;

    public function prunable()
    {
        return static::whereNotNull('read_at')->where('created_at', '<=', now()->subMonth());
    }
}