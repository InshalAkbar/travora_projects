<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reference',
        'destination_id',
        'destination_name',
        'destination_country',
        'full_name',
        'email',
        'phone',
        'travel_date',
        'return_date',
        'travelers',
        'travel_style',
        'special_requests',
        'total_price',
        'status',
    ];

    protected $casts = [
        'travel_date' => 'date',
        'return_date' => 'date',
        'total_price' => 'decimal:2',
    ];

    /**
     * Booking belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate unique booking reference
     */
    public static function generateReference()
    {
        do {
            $ref = 'TRV-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        } while (self::where('reference', $ref)->exists());

        return $ref;
    }
}