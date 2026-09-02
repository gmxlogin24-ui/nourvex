echo "Adding admin level/badge routes to web.php..."

cat >> routes/web.php << 'ROUTES'

// Admin Level & Badge Management Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Levels
    Route::prefix('levels')->name('levels.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AdminLevelBadgeController::class, 'levels'])->name('index');
        Route::get('/create', [\App\Http\Controllers\AdminLevelBadgeController::class, 'createLevel'])->name('create');
        Route::post('/', [\App\Http\Controllers\AdminLevelBadgeController::class, 'storeLevel'])->name('store');
        Route::get('/{level}/edit', [\App\Http\Controllers\AdminLevelBadgeController::class, 'editLevel'])->name('edit');
        Route::put('/{level}', [\App\Http\Controllers\AdminLevelBadgeController::class, 'updateLevel'])->name('update');
        Route::delete('/{level}', [\App\Http\Controllers\AdminLevelBadgeController::class, 'deleteLevel'])->name('destroy');
    });

    // Badges
    Route::prefix('badges')->name('badges.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AdminLevelBadgeController::class, 'badges'])->name('index');
        Route::get('/create', [\App\Http\Controllers\AdminLevelBadgeController::class, 'createBadge'])->name('create');
        Route::post('/', [\App\Http\Controllers\AdminLevelBadgeController::class, 'storeBadge'])->name('store');
        Route::get('/{badge}/edit', [\App\Http\Controllers\AdminLevelBadgeController::class, 'editBadge'])->name('edit');
        Route::put('/{badge}', [\App\Http\Controllers\AdminLevelBadgeController::class, 'updateBadge'])->name('update');
        Route::delete('/{badge}', [\App\Http\Controllers\AdminLevelBadgeController::class, 'deleteBadge'])->name('destroy');
    });

    // Refresh user levels/badges
    Route::post('/users/{user}/refresh-badges', [\App\Http\Controllers\AdminLevelBadgeController::class, 'refreshUser'])->name('users.refresh-badges');
});
ROUTES

echo "✅ Admin routes added to web.php"

echo ""
echo "Verifying routes were added..."
grep -A 25 "Admin Level & Badge Management Routes" routes/web.php

