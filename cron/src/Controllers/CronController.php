<?php

namespace Azuriom\Plugin\Cron\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class CronController extends Controller
{
    /**
     * Handle the cron execution request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request)
    {
        $key = setting('cron.key');

        if (empty($key)) {
            return response()->json(['error' => 'Cron key not configured.'], 500);
        }

        $bearer = $request->bearerToken();

        if (!$bearer) {
            $bearer = $request->input('key');
        }

        if (!hash_equals((string) $key, (string) $bearer)) {
            return response()->json(['error' => 'Invalid key.'], 403);
        }

        try {
            Artisan::call('schedule:run');
            $output = Artisan::output();

            $forcedOutput = $this->runForcedCommands();

            Setting::updateSettings('cron.last_executed_at', now()->toIso8601String());

            return response()->json([
                'success' => true,
                'message' => 'Cron tasks executed successfully.',
                'output' => $output.$forcedOutput,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while executing cron tasks.',
                'exception' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Force execution of critical commands that may be skipped by schedule:run
     * when invoked via HTTP (e.g. shop subscription expiration).
     *
     * Throttled to avoid running on every minute call.
     *
     * @return string
     */
    protected function runForcedCommands(): string
    {
        $output = '';
        $lastForced = setting('cron.last_forced_at');
        $shouldForce = !$lastForced
            || now()->diffInMinutes(\Illuminate\Support\Carbon::parse($lastForced)) >= 10;

        if (!$shouldForce) {
            return $output;
        }

        $commands = [
            'shop:subscriptions',
            'shop:payments',
        ];

        foreach ($commands as $command) {
            try {
                if (!array_key_exists($command, Artisan::all())) {
                    continue;
                }

                Artisan::call($command);
                $output .= "\n[forced:{$command}]\n".Artisan::output();
            } catch (\Exception $e) {
                $output .= "\n[forced:{$command}] failed: ".$e->getMessage();
            }
        }

        Setting::updateSettings('cron.last_forced_at', now()->toIso8601String());

        return $output;
    }

    /**
     * Handle the queue execution request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handleQueue(Request $request)
    {
        $key = setting('cron.key');

        if (empty($key)) {
            return response()->json(['error' => 'Cron key not configured.'], 500);
        }

        $bearer = $request->bearerToken();

        if (!$bearer) {
            $bearer = $request->input('key');
        }

        if (!hash_equals((string) $key, (string) $bearer)) {
            return response()->json(['error' => 'Invalid key.'], 403);
        }

        try {
            $output = shell_exec('php artisan queue:work --stop-when-empty');

            Setting::updateSettings('queue.last_executed_at', now()->toIso8601String());

            return response()->json([
                'success' => true,
                'message' => 'Queue processed successfully.',
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while processing queue.',
                'exception' => $e->getMessage(),
            ], 500);
        }
    }
}
