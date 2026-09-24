set -e

sed -i "s/^MAIL_SCHEME=.*/MAIL_SCHEME=null/" .env

echo "=== .env mail block after fix ==="
grep -E "^MAIL_" .env | sed -E 's/(MAIL_PASSWORD=)(.+)/\1[SET]/'

php artisan config:clear
php artisan cache:clear

echo ""
echo "=== Sending live test reset email ==="
php artisan tinker --execute="
\$user = \App\Models\User::where('email','emmanuelufuoma604@gmail.com')->first();
if (!\$user) { echo 'User not found'; } else {
    try {
        \$status = \Illuminate\Support\Facades\Password::sendResetLink(['email' => \$user->email]);
        echo 'Status: ' . \$status . PHP_EOL;
    } catch (\Throwable \$e) {
        echo 'EXCEPTION: ' . \$e->getMessage() . PHP_EOL;
    }
}
"
