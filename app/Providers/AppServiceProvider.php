<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Event;
use App\Events\TierUpgraded;
use App\Listeners\RecordTierUpgradeReveal;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (request()->header('X-Forwarded-Proto') === 'https' || request()->secure()) {
            URL::forceScheme('https');
        }

        Event::listen(TierUpgraded::class, RecordTierUpgradeReveal::class);

        View::composer('*', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                $view->with('navAvatar', $user->avatar_url);
                $view->with('navInitial', strtoupper(substr($user->name ?? 'U', 0, 1)));
                $view->with('navQCoinBalance', $user->q_coin_balance ?? 0);
                $unreadChats = \App\Models\ConversationParticipant::where('user_id', $user->id)
                    ->whereHas('conversation', fn($q) => $q->where('type', 'business'))
                    ->with('conversation')
                    ->get()
                    ->sum(fn($p) => $p->conversation->unreadCountFor($user->id));
                $view->with('navUnreadChats', $unreadChats);
                $view->with('navUnreadNotifications', $user->unreadNotifications()->count());
            }
        });
    }
}
