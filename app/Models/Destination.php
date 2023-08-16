<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'thumbnail'];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function updateRating()
    {
        $totalReviews = $this->reviews()->count();
        $totalRating = $this->reviews()->sum('rating');
        if ($totalReviews > 0) {
            $averageRating = $totalRating / $totalReviews;
            $this->average_rating = $averageRating;
            $this->save();
        }
    }
}
