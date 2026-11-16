<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'course_id',
        'content',
        'awaits_reply',
        'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'awaits_reply' => 'boolean',
            'replied_at' => 'datetime',
        ];
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Student messages still waiting on a reply after 24 hours
     */
    #[Scope]
    protected function overdue(Builder $query): void
    {
        $query->where('awaits_reply', true)
            ->whereNull('replied_at')
            ->where('created_at', '<', now()->subDay());
    }

    /**
     * Messages either way between two people
     */
     #[Scope]
    protected function between(Builder $query, int $userId, int $otherId): void
    {
        $query->where(fn ($q) => $q
            ->where(fn ($q) => $q->where('sender_id', $userId)->where('receiver_id', $otherId))
            ->orWhere(fn ($q) => $q->where('sender_id', $otherId)->where('receiver_id', $userId)));
    }
}