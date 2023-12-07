<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    use HasFactory;

    protected $fillable = [
        'educational_level',
        'field_of_study',
        'educational_institution',
        'from',
        'to',
        'currently_enrolled',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
