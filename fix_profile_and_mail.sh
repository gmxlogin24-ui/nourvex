set -e

# Fix double-hashing in profile password update (same bug as before)
sed -i "s/\$user->update(\['password' => Hash::make(\$validated\['password'\])\]);/\$user->update(['password' => \$validated['password']]);/" app/Http/Controllers/ProfileController.php

echo "=== ProfileController fix applied ==="
grep -n "'password' =>" app/Http/Controllers/ProfileController.php

# Switch .env from log driver to real Gmail SMTP
sed -i \
  -e "s/^MAIL_MAILER=.*/MAIL_MAILER=smtp/" \
  -e "s/^MAIL_HOST=.*/MAIL_HOST=smtp.gmail.com/" \
  -e "s/^MAIL_PORT=.*/MAIL_PORT=587/" \
  -e "s/^MAIL_SCHEME=.*/MAIL_SCHEME=tls/" \
  -e "s/^MAIL_USERNAME=.*/MAIL_USERNAME=emmanuelufuoma604@gmail.com/" \
  -e "s/^MAIL_PASSWORD=.*/MAIL_PASSWORD=\"nqor wqmq kpbd hhdg\"/" \
  -e "s/^MAIL_FROM_ADDRESS=.*/MAIL_FROM_ADDRESS=\"emmanuelufuoma604@gmail.com\"/" \
  .env

echo ""
echo "=== .env mail block after update (secrets masked) ==="
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
