<?php

namespace App\Http\Controllers\Api;

use App\Events\AuphonicStatusUpdated;
use App\Helpers\Auphonic\AuphonicStatus;
use App\Helpers\Auphonic\Exceptions\OptimizationFailedException;
use App\Http\Controllers\Controller;
use App\Jobs\CheckRemainingAuphonicOptimizationHours;
use App\Models\Episode;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WebhookController extends Controller
{
    /**
     * @var string
     */
    protected string $token;

    public function __construct()
    {
        $this->token = config('auphonic.token');
    }

    /**
     * @param  Request $request
     * @return Response
     * @throws OptimizationFailedException
     */
    public function __invoke(Request $request): Response
    {
        $request->validate([
            'uuid' => 'required|string',
            'status' => 'required|numeric',
            'status_string' => 'required|string',
        ]);

        $auphonicId = $request->get('uuid');
        $status = $request->get('status_string');
        $episode = Episode::where('auphonic_id', $auphonicId)->first();

        if (! $episode) {
            throw new ModelNotFoundException($auphonicId);
        }

        if ($status === 'Error') {
            $episode->update(['auphonic_status' => AuphonicStatus::FAILED]);
            AuphonicStatusUpdated::dispatch($episode);

            throw new OptimizationFailedException($auphonicId);
        }

        $auphonicProduction = Http::withHeaders([
            'Authorization' => "Bearer $this->token",
        ])->get("https://auphonic.com/api/production/$auphonicId.json")
            ->body();

        $auphonicProduction = json_decode($auphonicProduction, true);

        $downloadUrl = Arr::get($auphonicProduction, 'data.output_files.0.download_url');
        $format = Arr::get($auphonicProduction, 'data.output_files.0.format');

        Log::info($downloadUrl);

        $this->fetchAudioFile(
            $episode,
            $downloadUrl,
            $format,
        );

        $episode->update(['auphonic_status' => AuphonicStatus::COMPLETED]);
        AuphonicStatusUpdated::dispatch($episode);

        $this->deleteAuphonicProduction($auphonicId);

        CheckRemainingAuphonicOptimizationHours::dispatch();

        return response(['success' => true], 200);
    }

    /**
     * @param  Episode $episode
     * @param  string $downloadUrl
     * @param  string $extension
     * @return void
     */
    protected function fetchAudioFile(Episode $episode, string $downloadUrl, string $extension): void
    {
        $pathInfo = pathinfo($episode->audio_file);
        $filename = Arr::get($pathInfo, 'filename').'-optimized.'.$extension;
        $path = Arr::get($pathInfo, 'dirname');
        $disk = $episode->podcast->private_show ? 'private' : 'public';

        Storage::disk($disk)->put("$path/$filename", file_get_contents("$downloadUrl?bearer_token=$this->token"));

        $episode->update(['audio_file' => "$path/$filename"]);

        Storage::disk($disk)->delete($episode->storage_path);
    }

    /**
     * @param  string $auphonicId
     * @return void
     */
    protected function deleteAuphonicProduction(string $auphonicId): void
    {
        $token = config('auphonic.token');

        Http::withHeaders([
            'Authorization' => "Bearer $token",
        ])->delete("https://auphonic.com/api/production/$auphonicId.json");
    }
}
