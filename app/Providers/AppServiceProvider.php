<?php

namespace App\Providers;

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
        //
    }

     private function bindRepo()
    {
        $repoClass = [
            'Department',
        ];
        foreach ($repoClass as $repo_class) {
            $this->app->bind("App\Repositories\Interfaces\\{$repo_class}RepoInterface", "App\Repositories\\{$repo_class}Repo");
        }
    }
}
