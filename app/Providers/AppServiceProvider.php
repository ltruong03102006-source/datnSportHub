<?php

namespace App\Providers;

use App\Services\DebtService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Gateways\SettlementGatewayInterface::class, \App\Gateways\SimulatedSettlementGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['owner.*'], function ($view) {
            if (! Auth::check()) {
                return;
            }

            $user = Auth::user();
            $isOwner = false;

            if (method_exists($user, 'hasRole')) {
                $isOwner = $user->hasRole('owner');
            } elseif (isset($user->role)) {
                $isOwner = $user->role === 'owner';
            }

            if (! $isOwner) {
                return;
            }

            try {
                $view->with('ownerWalletSummary', app(DebtService::class)->getOwnerDebtSummary($user->id));
            } catch (\Throwable $exception) {
                report($exception);

                $view->with('ownerWalletSummary', null);
            }
        });
    }
}
