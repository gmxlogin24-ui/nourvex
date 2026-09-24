echo "=== layouts/app-admin.blade.php ==="
cat resources/views/layouts/app-admin.blade.php 2>&1
echo ""
echo "=== admin/disputes/index.blade.php ==="
cat resources/views/admin/disputes/index.blade.php 2>&1
echo ""
echo "=== admin/marketplace-disputes/index.blade.php ==="
cat resources/views/admin/marketplace-disputes/index.blade.php 2>&1
echo ""
echo "=== admin/users.blade.php (for table/checkbox style reference) ==="
cat resources/views/admin/users.blade.php 2>&1
echo ""
echo "=== partials/nav-admin.blade.php ==="
cat resources/views/partials/nav-admin.blade.php 2>&1
echo ""
echo "=== design-tokens.css ==="
find public -iname "design-tokens.css" -exec cat {} \; 2>&1
