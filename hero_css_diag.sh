grep -n -A 60 "\.profile-hero-card" public/css/profile-glass.css 2>&1
echo ""
echo "=== also checking dashboard2.css in case hero styles live there instead ==="
grep -n -A 60 "\.profile-hero-card" public/css/dashboard2.css 2>&1
