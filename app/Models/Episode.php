<?php

namespace App\Models;

use App\Helpers\Auphonic\Auphonic;
use App\Helpers\Auphonic\AuphonicSettings;
use App\Helpers\Auphonic\Exceptions\AudioAlgorithmsNotSetException;
use App\Helpers\Auphonic\Exceptions\OptimizationException;
use App\Helpers\Auphonic\Exceptions\ProductionNotFoundException;
use App\Helpers\Auphonic\Exceptions\ValidationException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $title
 * @property string $audio_file
 *
 * @property-read string $storage_path
 * @property-read  Podcast $podcast
 */
class Episode extends Model
{
    use HasFactory;

    // ================== //
    //     Attributes     //
    // ================== //

    protected $fillable = [
        'title',
        'audio_file',
        'auphonic_id',
        'auphonic_status',
    ];

    /**
     * @return string
     */
    public function getStoragePathAttribute(): string
    {
        $disk = $this->podcast->private_show ? 'private' : 'public';

        return "$disk/$this->audio_file";
    }

    // ================== //
    //     Methods        //
    // ================== //

    /**
     * @param  AuphonicSettings $settings
     *
     * @return void
     *
     * @throws AudioAlgorithmsNotSetException
     * @throws ValidationException
     * @throws ProductionNotFoundException
     * @throws GuzzleException
     * @throws OptimizationException
     */
    public function optimize(AuphonicSettings $settings): void
    {
        $auphonic = new Auphonic($this, $settings);

        $auphonic->setAudioAlgorithms()
            ->createProduction()
            ->uploadFile()
            ->optimize();
    }

    // ================== //
    //     Relations      //
    // ================== //

    /**
     * @return BelongsTo
     */
    public function podcast(): BelongsTo
    {
        return $this->belongsTo(Podcast::class);
    }
}
