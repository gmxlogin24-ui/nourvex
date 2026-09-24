echo "=== profile/index.blade.php ==="
cat resources/views/profile/index.blade.php 2>&1
echo ""
echo "=== partials/glass-balance-card.blade.php ==="
cat resources/views/partials/glass-balance-card.blade.php 2>&1
echo ""
echo "=== profile-glass.css ==="
find public -iname "profile-glass.css" -exec cat {} \; 2>&1
echo ""
echo "=== avatar_url / profile picture field on User model ==="
grep -n "avatar" app/Models/User.php 2>&1
echo ""
echo "=== ProfileController (relevant show/index method) ==="
grep -n -B 2 -A 20 "function index\|function account\|function appearance" app/Http/Controllers/ProfileController.php 2>&1
