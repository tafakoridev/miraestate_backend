<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentExpertise extends Model
{
    use HasFactory;

    protected $fillable = [
        'expertiese_id',
        'price',
        'field_id',
        'field_type',
    ];

    public function expertiese()
    {
        return $this->belongsTo(User::class);
    }

    public function field()
    {
        return $this->morphTo();
    }
}
