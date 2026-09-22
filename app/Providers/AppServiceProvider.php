<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->bindRepo();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePublicRateLimits();

        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            // Share data needed by the global contact section and footer on every page
            $sharedSettings = \App\Models\SiteSetting::pluck('value', 'key');
            $view->with([
                'promotions' => \App\Models\Promotion::visible()->orderBy('sort_order')->orderBy('id')->get(),
                'settings'   => $view->getData()['settings'] ?? $sharedSettings,
                'packages'   => $view->getData()['packages'] ?? \App\Models\Package::active()->get(['id', 'name']),
                'rooms'      => $view->getData()['rooms']    ?? \App\Models\Room::active()->orderBy('sort_order')->get(),
                'policies'   => $view->getData()['policies'] ?? \App\Models\Policy::active()->orderBy('category')->get()->keyBy('category'),
            ]);
        });
    }

    private function configurePublicRateLimits(): void
    {
        $response = function (Request $request, array $headers) {
            $seconds = max(1, (int) $headers['Retry-After']);
            $message = "Too many requests. Please wait {$seconds} seconds before trying again.";
            $headers['Cache-Control'] = 'private, no-store';

            return $request->expectsJson()
                ? response()->json(['message' => $message, 'retry_after' => $seconds], 429, $headers)
                : response()->view('errors.429', compact('message'), 429, $headers);
        };

        foreach ([
            'public-enquiries' => [5, 20],
            'public-currency' => [20, 100],
            'public-login' => [5, 30],
            'public-logout' => [20, 100],
            'public-details' => [60, 600],
        ] as $name => [$perMinute, $perHour]) {
            RateLimiter::for($name, fn (Request $request) => [
                // Both forms and failed validation attempts share the same IP quota.
                // Separate window keys prevent minute and hour counters from colliding.
                Limit::perMinute($perMinute)->by('minute:'.$request->ip())->response($response),
                Limit::perHour($perHour)->by('hour:'.$request->ip())->response($response),
            ]);
        }

        RateLimiter::for('login-account', function (Request $request) use ($response) {
            $email = $request->input('email');
            $key = hash('sha256', is_string($email) ? strtolower(trim($email)) : 'invalid');
            return [
                Limit::perMinute(10)->by('minute:'.$key)->response($response),
                Limit::perHour(100)->by('hour:'.$key)->response($response),
            ];
        });
    }

    private function bindRepo()
    {
        $repoClass = [];
        foreach ($repoClass as $repo_class) {
            $this->app->bind("App\Repositories\Interfaces\\{$repo_class}RepoInterface", "App\Repositories\\{$repo_class}Repo");
        }
    }
}
