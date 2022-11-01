<?php

namespace App\Helpers\Auphonic;

use App\Events\AuphonicStatusUpdated;
use App\Helpers\Auphonic\Exceptions\AudioAlgorithmsNotSetException;
use App\Helpers\Auphonic\Exceptions\OptimizationException;
use App\Helpers\Auphonic\Exceptions\OptimizationFailedException;
use App\Helpers\Auphonic\Exceptions\ProductionNotFoundException;
use App\Models\Episode;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Psr\Http\Message\ResponseInterface;

class Auphonic
{
    /**
     * @var Client
     */
    protected Client $client;

    /**
     * @var Episode
     */
    protected Episode $episode;

    /**
     * @var AuphonicSettings
     */
    protected AuphonicSettings $settings;

    /**
     * @var string
     */
    protected string $productionId;

    /**
     * Indicates whether the algorithms have been set in Auphonic or not.
     *
     * @var bool
     */
    protected bool $audioAlgorithmsSet;

    /**
     * @param  Episode $episode
     * @param  AuphonicSettings $settings
     */
    public function __construct(Episode $episode, AuphonicSettings $settings)
    {
        $token = config('auphonic.token');

        $this->episode = $episode;
        $this->settings = $settings;
        $this->client = new Client([
            'http_errors' => false,
            'base_uri' => config('auphonic.base_url'),
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "Bearer $token",
            ],
        ]);
    }

    /**
     * Create a new Auphonic production.
     *
     * @return $this
     * @throws GuzzleException
     * @throws Exception
     */
    public function createProduction(): self
    {
        Log::info('Creating production....', ['episode_id' => $this->episode->id]);

        $response = $this->client
            ->post('productions.json', [
                'json' => [
                    'metadata' => [
                        'title' => $this->episode->title,
                        'artist' => $this->episode->podcast->title,
                    ],
                    'webhook' => url('/api/auphonic'),
                ],
            ]);

        if ($response->getStatusCode() !== 200) {
            Log::error('Failed to create production.', [
                'episode_id' => $this->episode->id,
                'message' => $response->getBody()->getContents()
            ]);

            throw new OptimizationFailedException($response->getBody());
        }

        $data = $this->getRequestData($response);

        $this->productionId = Arr::get($data, 'uuid');
        $this->episode->update([
            'auphonic_id' => $this->productionId,
        ]);

        return $this;
    }

    /**
     * @return void
     * @throws ProductionNotFoundException
     * @throws GuzzleException
     */
    public function deleteProduction(): void
    {
        if (! $this->productionId) {
            throw new ProductionNotFoundException();
        }

        $response = $this->client
            ->delete("production/$this->productionId.json");

        if ($response->getStatusCode() !== 200) {
            Log::error('Failed to delete production.', [
                'episode_id' => $this->episode->id,
                'message' => $response->getBody()->getContents()
            ]);

            throw new ProductionNotFoundException('Deletion of production failed.');
        }
    }

    /**
     * Upload the audio file to Auphonic.
     *
     * @return $this
     * @throws GuzzleException
     * @throws Exception
     */
    public function uploadFile(): self
    {
        Log::info('uploading file to production...', [
            'episode_id' => $this->episode->id,
            'production_id' => $this->productionId,
        ]);

        $response = $this->client
            ->post("production/$this->productionId/upload.json", [
                'multipart' => [
                    [
                        'name' => 'input_file',
                        'contents' => Storage::get($this->episode->storage_path),
                        'filename' => basename($this->episode->storage_path),
                    ],
                ],
            ]);

        if ($response->getStatusCode() !== 200) {
            Log::error('Failed uploading file to Auphonic production.', [
                'episode_id' => $this->episode->id,
                'message' => $response->getBody()->getContents()
            ]);

            throw new OptimizationFailedException($response->getBody());
        }

        return $this;
    }

    /**
     * Set the audio algorithm settings for the Auponic production.
     *
     * @return self
     * @throws GuzzleException
     * @throws Exceptions\ValidationException
     */
    public function setAudioAlgorithms(): self
    {
        $this->settings->validate();

        $response = $this->client->post("production/$this->productionId.json", [
            'json' => [
                'output_files' => [
                    [
                        'format' => $this->settings->getFileFormat(),
                        'bitrate' => $this->settings->getBitrate(),
                    ],
                ],
                'algorithms' => [
                    'hipfilter' => $this->settings->isFiltering(),
                    'leveler' => $this->settings->isAdaptiveLeveling(),
                    'denoise' => $this->settings->isNoiseAndHumReducing(),
                    'denoiseamount' => $this->settings->getNoiseReductionAmount(),
                    'normloudness' => $this->settings->normalizesLoudness(),
                    'loudnesstarget' => $this->settings->getLoudnessTarget(),
                ],
            ],
        ]);

        $this->audioAlgorithmsSet = true;

        return $this;
    }

    /**
     * Start the optimization process.
     *
     * @return void
     * @throws AudioAlgorithmsNotSetException
     * @throws ProductionNotFoundException
     * @throws GuzzleException
     * @throws OptimizationException
     */
    public function optimize(): void
    {
        if (! $this->productionId) {
            Log::warning('Production not found.', ['episode_id' => $this->episode->id]);

            throw new ProductionNotFoundException();
        }

        if (! $this->audioAlgorithmsSet) {
            throw new AudioAlgorithmsNotSetException();
        }

        $response = $this->client
            ->post("production/$this->productionId/start.json");

        if ($response->getStatusCode() !== 200) {
            Log::error("uri: production/$this->productionId/start.json", [
                'status_code' => $response->getStatusCode(),
                'body' => $response->getBody(),
            ]);

            throw new OptimizationException($response->getBody());
        }

        $this->episode->update(['auphonic_status', AuphonicStatus::STARTED]);
        AuphonicStatusUpdated::dispatch($this->episode);
    }

    /**
     * @param  ResponseInterface $response
     * @return array
     */
    protected function getRequestData(ResponseInterface $response): array
    {
        $json = json_decode($response->getBody(), 1);

        return Arr::get($json, 'data');
    }
}
