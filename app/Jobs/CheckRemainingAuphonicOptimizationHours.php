<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckRemainingAuphonicOptimizationHours implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        $remainingTime = $this->getRemainingTime();

        if ($remainingTime < 10) {
           Log::warning('Auphonic optimization hours are low: ' . $remainingTime);
        }
    }

    /**
     * @return int
     */
    protected function getRemainingTime(): int
    {
        $token = config('auphonic.token');

        $user = Http::withHeaders([
            'Authorization' => "Bearer $token",
        ])->get(config('auphonic.base_url') .'/user.json')
            ->body();

        $data = json_decode($user, true);

        return intval(Arr::get($data, 'data.credits'));
    }
}
