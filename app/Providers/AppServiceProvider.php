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
        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            $view->with('promotions', \App\Models\Promotion::visible()->orderBy('sort_order')->orderBy('id')->get());
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
