<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class event extends Model
{
    use HasFactory;
    use HasUuids;


    protected $fillable = [
        'name',
        'agent_id',
        'date',
        'location',
        'sport_type',
        'description',
    ];
    

    
    public function Registrations () {
        return $this->hasMany(Registration::class, 'event_id', 'id');
    }

    
}
