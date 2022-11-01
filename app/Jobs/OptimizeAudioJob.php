<?php

namespace App\Jobs;

use App\Helpers\Auphonic\Auphonic;
use App\Helpers\Auphonic\AuphonicSettings;
use App\Helpers\Auphonic\Exceptions\AudioAlgorithmsNotSetException;
use App\Helpers\Auphonic\Exceptions\OptimizationException;
use App\Helpers\Auphonic\Exceptions\ProductionNotFoundException;
use App\Helpers\Auphonic\Exceptions\ValidationException;
use App\Models\Episode;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OptimizeAudioJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var Episode
     */
    private Episode $episode;

    /**
     * @var AuphonicSettings
     */
    private AuphonicSettings $settings;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Episode $episode, AuphonicSettings $settings)
    {
        $this->episode = $episode;
        $this->settings = $settings;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws AudioAlgorithmsNotSetException
     * @throws OptimizationException
     * @throws ProductionNotFoundException
     * @throws ValidationException
     * @throws GuzzleException
     */
    public function handle(): void
    {
        $auphonic = new Auphonic($this->episode, $this->settings);

        $auphonic->createProduction()
            ->uploadFile()
            ->setAudioAlgorithms()
            ->optimize();
    }
}
