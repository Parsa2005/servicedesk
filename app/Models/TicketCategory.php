<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TicketCategory extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'category_id');
    }

    /**
     * ارتباط با شرکت
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}