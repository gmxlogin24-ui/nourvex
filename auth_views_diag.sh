echo "=== auth/login.blade.php ==="
cat resources/views/auth/login.blade.php 2>&1
echo ""
echo "=== auth/register.blade.php ==="
cat resources/views/auth/register.blade.php 2>&1
echo ""
echo "=== neumorphic-auth.css ==="
find public -iname "neumorphic-auth.css" -exec cat {} \; 2>&1
