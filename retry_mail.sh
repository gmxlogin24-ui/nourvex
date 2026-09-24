set -e

echo "=== Clearing existing reset token throttle ==="
php artisan tinker --execute="
\DB::table('password_reset_tokens')->where('email','emmanuelufuoma604@gmail.com')->delete();
echo 'Cleared.';
"

echo ""
echo "=== Sending live test reset email ==="
php artisan tinker --execute="
\$user = \App\Models\User::where('email','emmanuelufuoma604@gmail.com')->first();
if (!\$user) { echo 'User not found'; } else {
    try {
        \$status = \Illuminate\Support\Facades\Password::sendResetLink(['email' => \$user->email]);
        echo 'Status: ' . \$status . PHP_EOL;
    } catch (\Throwable \$e) {
        echo 'EXCEPTION CLASS: ' . get_class(\$e) . PHP_EOL;
        echo 'EXCEPTION: ' . \$e->getMessage() . PHP_EOL;
    }
}
"
