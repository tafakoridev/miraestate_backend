<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auction extends Model
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

    public function agent()
    {
        return $this->morphOne(AgentDesk::class, 'agentable');
    }

    public function purpose()
    {
        return $this->morphMany(Purpose::class, 'purposeable');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
