echo "=== Does the new controller exist and have the right methods? ==="
grep -n "public function" app/Http/Controllers/Admin/SiteSettingsController.php 2>&1
echo ""
echo "=== Does the appearance view exist? ==="
ls -la resources/views/admin/settings/ 2>&1
echo ""
echo "=== Are the new routes registered? ==="
php artisan route:list | grep -i "settings.appearance"
echo ""
echo "=== Does login.blade.php have the side-by-side button? ==="
grep -n -A 2 "flex items-center gap-3" resources/views/auth/login.blade.php 2>&1
