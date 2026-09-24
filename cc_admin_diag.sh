echo "=== FeatureGate model ==="
cat app/Models/FeatureGate.php 2>&1
echo ""
echo "=== FeatureGateController ==="
cat app/Http/Controllers/Admin/FeatureGateController.php 2>&1
echo ""
echo "=== feature_gates migration ==="
cat database/migrations/2026_08_25_000004_create_feature_gates_table.php 2>&1
echo ""
echo "=== FeatureGateSeeder ==="
cat database/seeders/FeatureGateSeeder.php 2>&1
echo ""
echo "=== IsCustomerCareOrAdmin middleware ==="
cat app/Http/Middleware/IsCustomerCareOrAdmin.php 2>&1
echo ""
echo "=== CustomerCareController (full) ==="
cat app/Http/Controllers/CustomerCareController.php 2>&1
echo ""
echo "=== GigDispute model ==="
cat app/Models/GigDispute.php 2>&1
echo ""
echo "=== ListingDispute model ==="
cat app/Models/ListingDispute.php 2>&1
echo ""
echo "=== gig_disputes migration ==="
cat database/migrations/2026_08_05_120001_create_gig_disputes_table.php 2>&1
echo ""
echo "=== listing_disputes migration ==="
cat database/migrations/2026_08_16_073116_create_listing_disputes_table.php 2>&1
echo ""
echo "=== customer-care/admin related routes ==="
grep -n -i "customer-care\|dispute\|feature-gate" routes/web.php 2>&1
echo ""
echo "=== users table role/customer_care fields ==="
grep -n -A 10 "add_customer_care_fields\|add_role_to_users" database/migrations/2026_08_22_000000_add_role_to_users_table.php database/migrations/2026_08_22_020000_add_customer_care_fields.php 2>&1
