<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property bool $private_show
 *
 * @property-read Episode[] $episodes
 */
class Podcast extends Model
{
    use HasFactory;

    // ================== //
    //     Attributes     //
    // ================== //

    /**
     * @var string[] $fillable
     */
    protected $fillable = [
        'user_id',
        'title',
        'private_show',
    ];

    // ================== //
    //     Relations      //
    // ================== //

    /**
     * @return HasMany
     */
    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }
}
