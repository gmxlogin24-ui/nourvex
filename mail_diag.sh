echo "=== .env mail config (secrets masked) ==="
grep -E "^MAIL_" .env 2>&1 | sed -E 's/(MAIL_PASSWORD=)(.+)/\1[SET]/; s/(MAIL_USERNAME=)(.+)/\1[SET]/'
echo ""
echo "=== config/mail.php default mailer block ==="
grep -n -A 5 "'default'" config/mail.php 2>&1
echo ""
echo "=== Password reset notification class (if customized) ==="
find app -iname "*ResetPassword*" 2>&1
echo ""
echo "=== Queue driver (mail may be queued and stuck) ==="
grep -E "^QUEUE_CONNECTION" .env 2>&1
echo ""
echo "=== Failed/pending jobs table check ==="
php artisan tinker --execute="echo 'jobs table rows: ' . \DB::table('jobs')->count(); echo PHP_EOL; echo 'failed_jobs table rows: ' . \DB::table('failed_jobs')->count();" 2>&1
echo ""
echo "=== Try sending a real test reset and show any exception ==="
php artisan tinker --execute="
\$user = \App\Models\User::first();
if (!\$user) { echo 'No users found in DB'; } else {
    echo 'Testing reset for: ' . \$user->email . PHP_EOL;
    try {
        \$status = \Illuminate\Support\Facades\Password::sendResetLink(['email' => \$user->email]);
        echo 'Status: ' . \$status . PHP_EOL;
    } catch (\Throwable \$e) {
        echo 'EXCEPTION: ' . \$e->getMessage() . PHP_EOL;
        echo \$e->getTraceAsString();
    }
}
" 2>&1
echo ""
echo "=== Last 40 lines of laravel.log after test ==="
tail -n 40 storage/logs/laravel.log 2>&1
