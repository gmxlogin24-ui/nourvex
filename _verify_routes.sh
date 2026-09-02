echo "===== VERIFYING ALL ROUTES ====="
echo ""
echo "User-facing routes:"
grep -n "levels-badges\|qcoin" routes/web.php | head -20

echo ""
echo "Admin routes:"
grep -n "admin.*levels\|admin.*badges" routes/web.php | head -20

echo ""
echo "===== CHECKING MODEL RELATIONSHIPS ====="
php artisan tinker << 'TINKER'
echo "User relationships:\n";
$user = \App\Models\User::first();
echo "  - Has level: " . ($user->level ? 'Yes (' . $user->level->name . ')' : 'No') . "\n";
echo "  - Has badges: " . ($user->badges()->count()) . "\n";
echo "\nBadge sample:\n";
$badge = \App\Models\Badge::first();
echo "  - Name: " . $badge->name . "\n";
echo "  - Icon: " . $badge->icon . "\n";
echo "  - Users with badge: " . $badge->users()->count() . "\n";
TINKER

