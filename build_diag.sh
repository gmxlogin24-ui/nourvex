echo "=== GigDisputeController (full) ==="
cat app/Http/Controllers/GigDisputeController.php 2>&1
echo ""
echo "=== ListingDisputeController (full) ==="
cat app/Http/Controllers/ListingDisputeController.php 2>&1
echo ""
echo "=== Flag model ==="
cat app/Models/Flag.php 2>&1
echo ""
echo "=== flags migration ==="
find database/migrations -iname "*flag*" -exec cat {} \; 2>&1
echo ""
echo "=== User model isCustomerCareOrAdmin + related methods ==="
grep -n -B 2 -A 15 "isCustomerCareOrAdmin\|function isAdmin\|customer_care" app/Models/User.php 2>&1
echo ""
echo "=== AdminController toggleCustomerCare ==="
grep -n -B 2 -A 15 "function toggleCustomerCare" app/Http/Controllers/AdminController.php 2>&1
echo ""
echo "=== design-tokens.css ==="
find public -iname "design-tokens.css" -exec cat {} \; 2>&1
echo ""
echo "=== admin-shell.css ==="
find public -iname "admin-shell.css" -exec cat {} \; 2>&1
echo ""
echo "=== admin/index.blade.php (dashboard card style reference) ==="
cat resources/views/admin/index.blade.php 2>&1
