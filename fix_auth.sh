set -e

# Fix double-hashing: remove manual Hash::make() calls since the 'hashed' cast
# on User::password already handles hashing on assignment.
sed -i "s/'password' => Hash::make(\$validated\['password'\]),/'password' => \$validated['password'],/g" app/Http/Controllers/AuthController.php

sed -i "s/\$user->update(\['password' => Hash::make(\$password)\]);/\$user->update(['password' => \$password]);/" app/Http/Controllers/AuthController.php

echo "=== AuthController password lines after fix ==="
grep -n "'password' =>" app/Http/Controllers/AuthController.php

# Dedupe the google config block in services.php (remove the second identical block)
awk '
BEGIN{skip=0; count=0}
/^    '"'"'google'"'"' => \[/{count++; if(count==2){skip=1}}
skip==1 {
  if ($0 ~ /^    \],/) {skip=0}
  next
}
{print}
' config/services.php > config/services.php.tmp && mv config/services.php.tmp config/services.php

echo ""
echo "=== services.php google block after dedupe ==="
grep -n -A 4 "'google'" config/services.php

php artisan config:clear
php artisan cache:clear
