echo "=== AuthController ==="
cat app/Http/Controllers/AuthController.php 2>&1
echo ""
echo "=== SocialAuthController ==="
cat app/Http/Controllers/SocialAuthController.php 2>&1
echo ""
echo "=== User Model (fillable/hidden/casts) ==="
grep -n -A 30 "class User" app/Models/User.php 2>&1
echo ""
echo "=== Login/Auth routes ==="
grep -n -i "login\|password\|google" routes/web.php 2>&1
echo ""
echo "=== .env auth-related (secrets masked) ==="
grep -E "^(AUTH_|SESSION_|HASH_|GOOGLE_|APP_URL|APP_ENV|APP_DEBUG)" .env 2>&1 | sed -E 's/(=)(.+)/\1[SET]/'
echo ""
echo "=== config/auth.php ==="
cat config/auth.php 2>&1
echo ""
echo "=== config/services.php (google block) ==="
grep -n -A 6 "'google'" config/services.php 2>&1
echo ""
echo "=== Users table password/google_id columns ==="
php artisan tinker --execute="echo \Schema::hasColumn('users','password') ? 'password col: yes' : 'password col: NO'; echo PHP_EOL; echo \Schema::hasColumn('users','google_id') ? 'google_id col: yes' : 'google_id col: NO';" 2>&1
echo ""
echo "=== Session driver check ==="
php artisan config:show session.driver 2>&1
echo ""
echo "=== Recent auth-related errors in log ==="
tail -n 60 storage/logs/laravel.log 2>&1 | grep -i "auth\|password\|google\|hash"
