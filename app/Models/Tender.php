<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tender extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'department_id',
        'agent_id',
        'title',
        'description',
        'decline',
    ];

    public function agentUser()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function agent()
    {
        return $this->morphOne(AgentDesk::class, 'agentable');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function purpose()
    {
        return $this->morphMany(Purpose::class, 'purposeable');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
