<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Auphonic\AuphonicSettings;
use App\Helpers\Auphonic\Exceptions\NoiseReductionAmountNotSupportedException;
use App\Helpers\Auphonic\Exceptions\OutputFileNotSupportedException;
use App\Helpers\Auphonic\Exceptions\ValidationException;
use App\Http\Controllers\Controller;
use App\Jobs\OptimizeAudioJob;
use App\Models\Episode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EpisodeController extends Controller
{
    /**
     * @param  Episode $episode
     * @return JsonResponse
     * @throws ValidationException
     * @throws OutputFileNotSupportedException
     * @throws NoiseReductionAmountNotSupportedException
     */
    public function __invoke(Episode $episode): JsonResponse
    {
        $settings = new AuphonicSettings();

        $settings->setAdaptiveLeveler(true)
            ->setBitrate(128)
            ->setFileFormat('mp3')
            ->setFiltering(true)
            ->setLoudnessNormalization(true)
            ->setNoiseAndHumReduction(true)
            ->setNoiseReductionAmount(0.5)
            ->setNoiseAndHumReduction(true);

        OptimizeAudioJob::dispatch($episode, $settings);

        return response()->json(['message' => 'Optimization queued.']);
    }
}
