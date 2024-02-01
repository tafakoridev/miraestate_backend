<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentDesk extends Model
{
    use HasFactory;

    protected $fillable = ['description', 'agent_id', 'fields', 'rate', 'comment', 'agentable_id', 'accepted'];


    public function agentable()
    {
        return $this->morphTo();
    }

    public function agent()
    {
        return $this->belongsTo(User::class);
    }
}
