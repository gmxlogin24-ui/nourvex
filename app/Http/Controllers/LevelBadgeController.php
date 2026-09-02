<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Level;
use App\Services\LevelBadgeService;
use Illuminate\Http\Request;

class LevelBadgeController extends Controller
{
    public function __construct(protected LevelBadgeService $service) {}

    /** Shared context used by every page on this feature. */
    protected function context(Request $request): array
    {
        $user = $request->user();
        $result = $this->service->refresh($user);
        $metrics = $result['metrics'];

        $owned = $user->badges()->pluck('badges.id')->all();

        $badges = Badge::active()->orderBy('id')->get()
            ->map(fn (Badge $badge) => [
                'badge' => $badge,
                'owned' => in_array($badge->id, $owned),
                'progress' => $this->service->badgeProgress($badge, $metrics),
            ]);

        $nextLevel = $this->service->nextLevel($metrics);

        return [
            'metrics' => $metrics,
            'currentLevel' => $result['level'],
            'nextLevel' => $nextLevel,
            'levelProgress' => $this->service->levelProgress($nextLevel, $metrics),
            'levels' => Level::active()->orderBy('rank')->get(),
            'badges' => $badges,
            'earnedCount' => count($owned),
            'newBadges' => $result['new_badges'],
        ];
    }

    public function show(Request $request)
    {
        $ctx = $this->context($request);

        return view('levels-badges.show', [
            'currentLevel' => $ctx['currentLevel'],
            'nextLevel' => $ctx['nextLevel'],
            'levelProgress' => $ctx['levelProgress'],
            'badges' => $ctx['badges'],
            'badgesEarned' => $ctx['earnedCount'],
            'totalBadges' => $ctx['badges']->count(),
        ]);
    }

    public function badges(Request $request)
    {
        $ctx = $this->context($request);

        return view('levels-badges.badges', [
            'badges' => $ctx['badges'],
            'earnedBadges' => $ctx['earnedCount'],
            'totalBadges' => $ctx['badges']->count(),
        ]);
    }

    public function levels(Request $request)
    {
        $ctx = $this->context($request);

        return view('levels-badges.levels', [
            'levels' => $ctx['levels'],
            'currentLevelId' => $ctx['currentLevel']?->id,
        ]);
    }
}
