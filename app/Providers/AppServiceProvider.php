<?php

namespace App\Providers;

use App\Features\Eclat\Interfaces\EclatRepositoryInterface;
use App\Features\Eclat\Repositories\EclatRepository;
use App\Features\Reports\Interfaces\ReportRepositoryInterface;
use App\Features\Reports\Repositories\ReportRepository;
use App\Features\Snacks\Interfaces\SnackRepositoryInterface;
use App\Features\Snacks\Repositories\SnackRepository;
use App\Features\Transactions\Interfaces\TransactionRepositoryInterface;
use App\Features\Transactions\Repositories\TransactionRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SnackRepositoryInterface::class, SnackRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
        $this->app->bind(EclatRepositoryInterface::class, EclatRepository::class);
        $this->app->bind(ReportRepositoryInterface::class, ReportRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
