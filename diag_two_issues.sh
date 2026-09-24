echo "=== Current .auth-split-shell block in auth-split.css ==="
grep -n -B 2 -A 15 "\.auth-split-shell {" public/css/auth-split.css
echo ""
echo "=== Search for any existing logout link/form across views ==="
grep -rn "route('logout')" resources/views/ 2>&1
echo ""
echo "=== nav-rail.blade.php (likely sidebar) ==="
cat resources/views/partials/nav-rail.blade.php 2>&1
