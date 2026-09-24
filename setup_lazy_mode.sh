set -e

echo "=== Creating migration ==="
php artisan make:migration add_assignment_and_permissions_columns
MIGRATION_FILE=$(ls -t database/migrations/*add_assignment_and_permissions_columns.php | head -1)
cat > "$MIGRATION_FILE" << 'MIGEOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('gig_disputes') && !Schema::hasColumn('gig_disputes', 'assigned_to')) {
            Schema::table('gig_disputes', function (Blueprint $table) {
                $table->foreignId('assigned_to')->nullable()->after('raised_by')->constrained('users')->nullOnDelete();
                $table->timestamp('assigned_at')->nullable()->after('assigned_to');
            });
        }

        if (Schema::hasTable('listing_disputes') && !Schema::hasColumn('listing_disputes', 'assigned_to')) {
            Schema::table('listing_disputes', function (Blueprint $table) {
                $table->foreignId('assigned_to')->nullable()->after('raised_by')->constrained('users')->nullOnDelete();
                $table->timestamp('assigned_at')->nullable()->after('assigned_to');
            });
        }

        if (Schema::hasTable('flags') && !Schema::hasColumn('flags', 'assigned_to')) {
            Schema::table('flags', function (Blueprint $table) {
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('assigned_at')->nullable();
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'customer_care_permissions')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('customer_care_permissions')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('gig_disputes', 'assigned_to')) {
            Schema::table('gig_disputes', function (Blueprint $table) {
                $table->dropConstrainedForeignId('assigned_to');
                $table->dropColumn('assigned_at');
            });
        }
        if (Schema::hasColumn('listing_disputes', 'assigned_to')) {
            Schema::table('listing_disputes', function (Blueprint $table) {
                $table->dropConstrainedForeignId('assigned_to');
                $table->dropColumn('assigned_at');
            });
        }
        if (Schema::hasColumn('flags', 'assigned_to')) {
            Schema::table('flags', function (Blueprint $table) {
                $table->dropConstrainedForeignId('assigned_to');
                $table->dropColumn('assigned_at');
            });
        }
        if (Schema::hasColumn('users', 'customer_care_permissions')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('customer_care_permissions');
            });
        }
    }
};
MIGEOF
echo "Migration written: $MIGRATION_FILE"

echo ""
echo "=== Writing DisputeAssignmentController ==="
mkdir -p app/Http/Controllers/Admin
cat > app/Http/Controllers/Admin/DisputeAssignmentController.php << 'CTRLEOF'
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Flag;
use App\Models\GigDispute;
use App\Models\ListingDispute;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisputeAssignmentController extends Controller
{
    private const MODELS = [
        'gig' => GigDispute::class,
        'listing' => ListingDispute::class,
        'flag' => Flag::class,
    ];

    public function assign(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $validated = $request->validate([
            'type' => 'required|in:gig,listing,flag',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
            'agent_id' => 'required|exists:users,id',
        ]);

        $agent = User::findOrFail($validated['agent_id']);
        abort_unless($agent->isCustomerCare(), 422, 'Selected user is not a customer care agent.');

        $modelClass = self::MODELS[$validated['type']];

        $modelClass::whereIn('id', $validated['ids'])->update([
            'assigned_to' => $agent->id,
            'assigned_at' => now(),
        ]);

        $count = count($validated['ids']);
        return back()->with('status', $count . ' item(s) assigned to ' . $agent->name . '.');
    }
}
CTRLEOF

echo ""
echo "=== Writing CustomerCarePermissionController ==="
cat > app/Http/Controllers/Admin/CustomerCarePermissionController.php << 'CTRLEOF'
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerCarePermissionController extends Controller
{
    private const VALID_KEYS = ['orders', 'gig_disputes', 'listing_disputes', 'user_lookup', 'flags'];

    public function update(Request $request, User $user)
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        abort_unless($user->isCustomerCare(), 422, 'This user is not a customer care agent.');

        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'in:' . implode(',', self::VALID_KEYS),
        ]);

        $user->update(['customer_care_permissions' => $validated['permissions'] ?? []]);

        return back()->with('status', 'Permissions updated for ' . $user->name . '.');
    }
}
CTRLEOF

echo ""
echo "=== Writing lazy-mode-bar partial ==="
cat > resources/views/partials/lazy-mode-bar.blade.php << 'VIEWEOF'
@php $wrapId = 'lazy-wrap-' . $assignType; @endphp
<div style="position:fixed;bottom:0;left:72px;right:0;z-index:60;display:flex;justify-content:flex-end;padding:1rem;pointer-events:none;">
    <div id="{{ $wrapId }}-panel" class="stat-card" data-assign-url="{{ route('admin.disputes.assign') }}"
        style="pointer-events:auto;display:none;flex-direction:column;gap:0.6rem;padding:0.8rem 1rem;min-width:280px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <strong style="font-size:0.85rem;">Lazy Mode</strong>
            <span id="{{ $wrapId }}-progress" style="font-size:0.75rem;color:var(--color-muted);"></span>
        </div>
        <select id="{{ $wrapId }}-agent" class="balance-chip" style="width:100%;background:var(--color-card);border:1px solid var(--border-color);">
            <option value="">Select customer care agent…</option>
            @foreach($ccAgents as $agent)
                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
            @endforeach
        </select>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
            <button type="button" class="balance-chip primary" style="font-size:0.75rem;" onclick="lazyPushAll('{{ $assignType }}','{{ $wrapId }}')">Push All to Agent</button>
            <button type="button" class="balance-chip" style="font-size:0.75rem;" onclick="lazyStartOneByOne('{{ $assignType }}','{{ $wrapId }}')">One by One</button>
        </div>
        <div id="{{ $wrapId }}-step-controls" style="display:none;gap:0.5rem;flex-wrap:wrap;">
            <button type="button" class="balance-chip primary" style="font-size:0.75rem;" onclick="lazyAssignCurrent('{{ $assignType }}','{{ $wrapId }}')">Assign &amp; Next</button>
            <button type="button" class="balance-chip" style="font-size:0.75rem;" onclick="lazySkipCurrent('{{ $wrapId }}')">Skip</button>
        </div>
        <button type="button" class="balance-chip" style="font-size:0.7rem;align-self:flex-end;" onclick="lazyClosePanel('{{ $wrapId }}')">Close</button>
    </div>
    <button type="button" style="pointer-events:auto;font-size:0.8rem;margin-left:0.5rem;box-shadow:var(--shadow-medium);" class="balance-chip primary" onclick="lazyTogglePanel('{{ $wrapId }}')">😴 Lazy Mode</button>
</div>
VIEWEOF

echo ""
echo "=== Writing lazy-mode.js ==="
mkdir -p public/js
cat > public/js/lazy-mode.js << 'JSEOF'
function lazyTogglePanel(wrapId) {
    const panel = document.getElementById(wrapId + '-panel');
    panel.style.display = panel.style.display === 'none' || !panel.style.display ? 'flex' : 'none';
}
function lazyClosePanel(wrapId) {
    document.getElementById(wrapId + '-panel').style.display = 'none';
    lazyExitOneByOne(wrapId);
}
function lazyGetCards(wrapId) {
    return Array.from(document.querySelectorAll('[data-lazy-card="' + wrapId + '"]'));
}
function lazyCsrf() {
    return document.querySelector('meta[name="csrf-token"]').content;
}
async function lazyPostAssign(wrapId, type, ids, agentId) {
    const url = document.getElementById(wrapId + '-panel').dataset.assignUrl;
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': lazyCsrf(), 'Accept': 'application/json' },
        body: JSON.stringify({ type: type, ids: ids, agent_id: agentId })
    });
    return res.ok;
}
async function lazyPushAll(type, wrapId) {
    const agentId = document.getElementById(wrapId + '-agent').value;
    if (!agentId) { alert('Pick an agent first.'); return; }
    const ids = lazyGetCards(wrapId).map(c => c.dataset.disputeId);
    if (!ids.length) { alert('Nothing to assign.'); return; }
    if (!confirm('Assign all ' + ids.length + ' item(s) to this agent?')) return;
    const ok = await lazyPostAssign(wrapId, type, ids, agentId);
    if (ok) location.reload(); else alert('Assignment failed.');
}
let lazyIdx = {};
function lazyStartOneByOne(type, wrapId) {
    const agentId = document.getElementById(wrapId + '-agent').value;
    if (!agentId) { alert('Pick an agent first.'); return; }
    const cards = lazyGetCards(wrapId);
    if (!cards.length) { alert('Nothing to review.'); return; }
    lazyIdx[wrapId] = 0;
    document.getElementById(wrapId + '-step-controls').style.display = 'flex';
    lazyRenderStep(wrapId);
}
function lazyRenderStep(wrapId) {
    const cards = lazyGetCards(wrapId);
    const idx = lazyIdx[wrapId] || 0;
    cards.forEach((c, i) => c.style.display = i === idx ? '' : 'none');
    document.getElementById(wrapId + '-progress').textContent = idx < cards.length ? (idx + 1) + ' / ' + cards.length : 'All reviewed';
}
async function lazyAssignCurrent(type, wrapId) {
    const agentId = document.getElementById(wrapId + '-agent').value;
    if (!agentId) { alert('Pick an agent first.'); return; }
    const cards = lazyGetCards(wrapId);
    const idx = lazyIdx[wrapId] || 0;
    if (idx >= cards.length) return;
    const ok = await lazyPostAssign(wrapId, type, [cards[idx].dataset.disputeId], agentId);
    if (ok) { lazyIdx[wrapId] = idx + 1; lazyRenderStep(wrapId); } else { alert('Assignment failed.'); }
}
function lazySkipCurrent(wrapId) {
    lazyIdx[wrapId] = (lazyIdx[wrapId] || 0) + 1;
    lazyRenderStep(wrapId);
}
function lazyExitOneByOne(wrapId) {
    lazyGetCards(wrapId).forEach(c => { c.style.display = ''; });
    document.getElementById(wrapId + '-step-controls').style.display = 'none';
}
JSEOF

echo ""
echo "=== Applying edits to existing files ==="
cat > maintenance_lazy_mode.php << 'PHPEOF'
<?php
function edit($file, $old, $new, $label) {
    if (!file_exists($file)) { echo "[MISSING FILE] $file ($label)\n"; return; }
    $content = file_get_contents($file);
    $count = substr_count($content, $old);
    if ($count === 0) { echo "[ANCHOR NOT FOUND] $file :: $label\n"; return; }
    $content = str_replace($old, $new, $content);
    file_put_contents($file, $content);
    echo "[OK] $file :: $label\n";
}

// ---- User model ----
$old = <<<'EOD'
            'interests' => 'array',
EOD;
$new = <<<'EOD'
            'interests' => 'array',
            'customer_care_permissions' => 'array',
EOD;
edit('app/Models/User.php', $old, $new, 'casts');

$old = <<<'EOD'
    public function isCustomerCareOrAdmin(): bool
    {
        return $this->isAdmin() || $this->isCustomerCare();
    }
}
EOD;
$new = <<<'EOD'
    public function isCustomerCareOrAdmin(): bool
    {
        return $this->isAdmin() || $this->isCustomerCare();
    }

    public function hasCcPermission(string $key): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        return in_array($key, $this->customer_care_permissions ?? [], true);
    }
}
EOD;
edit('app/Models/User.php', $old, $new, 'hasCcPermission');

// ---- GigDispute model ----
$old = <<<'EOD'
    protected $fillable = [
        'gig_id', 'raised_by', 'description', 'status', 'resolved_by', 'resolved_at',
    ];
EOD;
$new = <<<'EOD'
    protected $fillable = [
        'gig_id', 'raised_by', 'description', 'status', 'resolved_by', 'resolved_at', 'assigned_to', 'assigned_at',
    ];
EOD;
edit('app/Models/GigDispute.php', $old, $new, 'fillable');

$old = <<<'EOD'
    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }
EOD;
$new = <<<'EOD'
    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
            'assigned_at' => 'datetime',
        ];
    }
EOD;
edit('app/Models/GigDispute.php', $old, $new, 'casts');
edit('app/Models/ListingDispute.php', $old, $new, 'casts');

$old = <<<'EOD'
    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
EOD;
$new = <<<'EOD'
    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
EOD;
edit('app/Models/GigDispute.php', $old, $new, 'assignedTo relation');
edit('app/Models/ListingDispute.php', $old, $new, 'assignedTo relation');

// ---- ListingDispute fillable ----
$old = <<<'EOD'
    protected $fillable = [
        'listing_id', 'raised_by', 'description', 'status', 'resolved_by', 'resolved_at',
    ];
EOD;
$new = <<<'EOD'
    protected $fillable = [
        'listing_id', 'raised_by', 'description', 'status', 'resolved_by', 'resolved_at', 'assigned_to', 'assigned_at',
    ];
EOD;
edit('app/Models/ListingDispute.php', $old, $new, 'fillable');

// ---- Flag model ----
$old = <<<'EOD'
#[Fillable(['flaggable_type', 'flaggable_id', 'reason', 'raised_by', 'status'])]
class Flag extends Model
{
    public function raisedBy()
EOD;
$new = <<<'EOD'
#[Fillable(['flaggable_type', 'flaggable_id', 'reason', 'raised_by', 'status', 'assigned_to'])]
class Flag extends Model
{
    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
        ];
    }

    public function raisedBy()
EOD;
edit('app/Models/Flag.php', $old, $new, 'fillable + casts');

$old = <<<'EOD'
    public function flaggable()
    {
        return $this->morphTo();
    }
}
EOD;
$new = <<<'EOD'
    public function flaggable()
    {
        return $this->morphTo();
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
EOD;
edit('app/Models/Flag.php', $old, $new, 'assignedTo relation');

// ---- routes/web.php ----
$old = <<<'EOD'
        Route::post('/marketplace-disputes/{dispute}/dismiss', [App\Http\Controllers\ListingDisputeController::class, 'dismiss'])->name('marketplace-disputes.dismiss');
EOD;
$new = <<<'EOD'
        Route::post('/marketplace-disputes/{dispute}/dismiss', [App\Http\Controllers\ListingDisputeController::class, 'dismiss'])->name('marketplace-disputes.dismiss');
        Route::post('/disputes/assign', [App\Http\Controllers\Admin\DisputeAssignmentController::class, 'assign'])->name('disputes.assign');
EOD;
edit('routes/web.php', $old, $new, 'disputes.assign route');

$old = <<<'EOD'
        Route::post('/users/{user}/toggle-customer-care', [AdminController::class, 'toggleCustomerCare'])->name('toggle-customer-care');
EOD;
$new = <<<'EOD'
        Route::post('/users/{user}/toggle-customer-care', [AdminController::class, 'toggleCustomerCare'])->name('toggle-customer-care');
        Route::put('/users/{user}/cc-permissions', [App\Http\Controllers\Admin\CustomerCarePermissionController::class, 'update'])->name('users.cc-permissions');
EOD;
edit('routes/web.php', $old, $new, 'cc-permissions route');

// ---- CustomerCareController ----
$old = <<<'EOD'
    public function orders(Request $r)
    {
        $q = ListingOrder::with(['listing', 'buyer'])->latest();
        if ($r->filled('search')) {
            $q->where('tracking_id', 'like', "%{$r->search}%");
        }
        return view('customer-care.orders', ['orders' => $q->paginate(25)->withQueryString()]);
    }
EOD;
$new = <<<'EOD'
    public function orders(Request $r)
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || $user->hasCcPermission('orders'), 403);

        $q = ListingOrder::with(['listing', 'buyer'])->latest();
        if ($r->filled('search')) {
            $q->where('tracking_id', 'like', "%{$r->search}%");
        }
        return view('customer-care.orders', ['orders' => $q->paginate(25)->withQueryString()]);
    }
EOD;
edit('app/Http/Controllers/CustomerCareController.php', $old, $new, 'orders permission');

$old = <<<'EOD'
    public function gigDisputes()
    {
        return view('customer-care.gig-disputes', ['disputes' => GigDispute::with(['gig', 'raisedBy'])->latest()->paginate(25)]);
    }
EOD;
$new = <<<'EOD'
    public function gigDisputes()
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || $user->hasCcPermission('gig_disputes'), 403);

        $q = GigDispute::with(['gig', 'raisedBy', 'assignedTo'])->latest();
        if (!$user->isAdmin()) {
            $q->where(function ($sub) use ($user) {
                $sub->whereNull('assigned_to')->orWhere('assigned_to', $user->id);
            });
        }
        return view('customer-care.gig-disputes', ['disputes' => $q->paginate(25)]);
    }
EOD;
edit('app/Http/Controllers/CustomerCareController.php', $old, $new, 'gigDisputes permission + filter');

$old = <<<'EOD'
    public function listingDisputes()
    {
        return view('customer-care.listing-disputes', ['disputes' => ListingDispute::with(['listing', 'raisedBy'])->latest()->paginate(25)]);
    }
EOD;
$new = <<<'EOD'
    public function listingDisputes()
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || $user->hasCcPermission('listing_disputes'), 403);

        $q = ListingDispute::with(['listing', 'raisedBy', 'assignedTo'])->latest();
        if (!$user->isAdmin()) {
            $q->where(function ($sub) use ($user) {
                $sub->whereNull('assigned_to')->orWhere('assigned_to', $user->id);
            });
        }
        return view('customer-care.listing-disputes', ['disputes' => $q->paginate(25)]);
    }
EOD;
edit('app/Http/Controllers/CustomerCareController.php', $old, $new, 'listingDisputes permission + filter');

$old = <<<'EOD'
    public function userLookup(Request $r)
    {
        $user = null;
        if ($r->filled('search')) {
            $user = \App\Models\User::where('email', $r->search)->orWhere('name', 'like', "%{$r->search}%")->first();
        }
        return view('customer-care.user-lookup', ['user' => $user, 'search' => $r->search]);
    }
EOD;
$new = <<<'EOD'
    public function userLookup(Request $r)
    {
        $authUser = Auth::user();
        abort_unless($authUser->isAdmin() || $authUser->hasCcPermission('user_lookup'), 403);

        $user = null;
        if ($r->filled('search')) {
            $user = \App\Models\User::where('email', $r->search)->orWhere('name', 'like', "%{$r->search}%")->first();
        }
        return view('customer-care.user-lookup', ['user' => $user, 'search' => $r->search]);
    }
EOD;
edit('app/Http/Controllers/CustomerCareController.php', $old, $new, 'userLookup permission');

// ---- GigDisputeController::index ----
$old = <<<'EOD'
    public function index()
    {
        abort_unless(Auth::user()->is_admin, 403);
        $disputes = GigDispute::where('status', 'open')->with(['gig', 'raisedBy'])->latest()->get();
        return view('admin.disputes.index', ['disputes' => $disputes]);
    }
EOD;
$new = <<<'EOD'
    public function index()
    {
        abort_unless(Auth::user()->is_admin, 403);
        $disputes = GigDispute::where('status', 'open')->with(['gig', 'raisedBy', 'assignedTo'])->latest()->get();
        $ccAgents = User::where('role', 'customer_care')->orderBy('name')->get();
        return view('admin.disputes.index', ['disputes' => $disputes, 'ccAgents' => $ccAgents]);
    }
EOD;
edit('app/Http/Controllers/GigDisputeController.php', $old, $new, 'index ccAgents');

// ---- ListingDisputeController::index ----
$old = <<<'EOD'
    public function index()
    {
        abort_unless(Auth::user()->is_admin, 403);
        $disputes = ListingDispute::where('status', 'open')->with(['listing', 'raisedBy'])->latest()->get();
        return view('admin.marketplace-disputes.index', ['disputes' => $disputes]);
    }
EOD;
$new = <<<'EOD'
    public function index()
    {
        abort_unless(Auth::user()->is_admin, 403);
        $disputes = ListingDispute::where('status', 'open')->with(['listing', 'raisedBy', 'assignedTo'])->latest()->get();
        $ccAgents = User::where('role', 'customer_care')->orderBy('name')->get();
        return view('admin.marketplace-disputes.index', ['disputes' => $disputes, 'ccAgents' => $ccAgents]);
    }
EOD;
edit('app/Http/Controllers/ListingDisputeController.php', $old, $new, 'index ccAgents');

// ---- Layout ----
$old = <<<'EOD'
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qelvo Admin - @yield('title', 'Dashboard')</title>
EOD;
$new = <<<'EOD'
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Qelvo Admin - @yield('title', 'Dashboard')</title>
EOD;
edit('resources/views/layouts/app-admin.blade.php', $old, $new, 'csrf meta');

$old = <<<'EOD'
    <script src="{{ asset('js/dispute-ring.js') }}?v={{ time() }}"></script>
    @yield('scripts')
EOD;
$new = <<<'EOD'
    <script src="{{ asset('js/dispute-ring.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/lazy-mode.js') }}?v={{ time() }}"></script>
    @yield('scripts')
EOD;
edit('resources/views/layouts/app-admin.blade.php', $old, $new, 'lazy-mode.js include');

// ---- admin/disputes/index.blade.php ----
$old = <<<'EOD'
        @foreach($disputes as $dispute)
            @php $remaining = $dispute->gig->amount - $dispute->gig->escrowed_amount; @endphp
            <div class="stat-card">
EOD;
$new = <<<'EOD'
        @foreach($disputes as $dispute)
            @php $remaining = $dispute->gig->amount - $dispute->gig->escrowed_amount; @endphp
            <div class="stat-card" data-lazy-card="lazy-wrap-gig" data-dispute-id="{{ $dispute->id }}">
EOD;
edit('resources/views/admin/disputes/index.blade.php', $old, $new, 'card data attrs');

$old = <<<'EOD'
    @else
    <div class="stat-card text-center text-muted py-8">No open disputes.</div>
    @endif
</div>
@endsection
EOD;
$new = <<<'EOD'
    @else
    <div class="stat-card text-center text-muted py-8">No open disputes.</div>
    @endif

    @include('partials.lazy-mode-bar', ['ccAgents' => $ccAgents, 'assignType' => 'gig'])
</div>
@endsection
EOD;
edit('resources/views/admin/disputes/index.blade.php', $old, $new, 'lazy bar include');

// ---- admin/marketplace-disputes/index.blade.php ----
$old = <<<'EOD'
        @foreach($disputes as $dispute)
            <div class="stat-card">
EOD;
$new = <<<'EOD'
        @foreach($disputes as $dispute)
            <div class="stat-card" data-lazy-card="lazy-wrap-listing" data-dispute-id="{{ $dispute->id }}">
EOD;
edit('resources/views/admin/marketplace-disputes/index.blade.php', $old, $new, 'card data attrs');

$old2 = <<<'EOD'
    @else
    <div class="stat-card text-center text-muted py-8">No open disputes.</div>
    @endif
</div>
@endsection
EOD;
$new2 = <<<'EOD'
    @else
    <div class="stat-card text-center text-muted py-8">No open disputes.</div>
    @endif

    @include('partials.lazy-mode-bar', ['ccAgents' => $ccAgents, 'assignType' => 'listing'])
</div>
@endsection
EOD;
edit('resources/views/admin/marketplace-disputes/index.blade.php', $old2, $new2, 'lazy bar include');

// ---- admin/users.blade.php ----
$old = <<<'EOD'
                        @if(!$u->is_admin)
                        <form method="POST" action="{{ route('admin.toggle-customer-care', $u) }}">
                            @csrf
                            <button class="balance-chip" style="font-size:0.75rem;">{{ $u->role === 'customer_care' ? 'Remove Customer Care' : 'Make Customer Care' }}</button>
                        </form>
                        @endif
EOD;
$new = <<<'EOD'
                        @if(!$u->is_admin)
                        <form method="POST" action="{{ route('admin.toggle-customer-care', $u) }}">
                            @csrf
                            <button class="balance-chip" style="font-size:0.75rem;">{{ $u->role === 'customer_care' ? 'Remove Customer Care' : 'Make Customer Care' }}</button>
                        </form>
                        @endif
                        @if($u->role === 'customer_care')
                        <form method="POST" action="{{ route('admin.users.cc-permissions', $u) }}" class="flex flex-wrap items-center gap-2" style="font-size:0.7rem;background:var(--color-card);border:1px solid var(--border-color);border-radius:0.5rem;padding:0.3rem 0.5rem;">
                            @csrf
                            @method('PUT')
                            @php $perms = $u->customer_care_permissions ?? []; @endphp
                            @foreach(['orders'=>'Orders','gig_disputes'=>'Gig Disp.','listing_disputes'=>'Mkt Disp.','user_lookup'=>'Lookup','flags'=>'Flags'] as $key => $label)
                                <label style="display:flex;align-items:center;gap:2px;">
                                    <input type="checkbox" name="permissions[]" value="{{ $key }}" @checked(in_array($key, $perms))>
                                    {{ $label }}
                                </label>
                            @endforeach
                            <button class="balance-chip primary" style="font-size:0.7rem;">Save Access</button>
                        </form>
                        @endif
EOD;
edit('resources/views/admin/users.blade.php', $old, $new, 'permission checkboxes');

echo "\nDone applying edits.\n";
PHPEOF

php maintenance_lazy_mode.php
rm maintenance_lazy_mode.php

echo ""
echo "=== Running migration ==="
php artisan migrate --force

echo ""
echo "=== Clearing caches ==="
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo ""
echo "=== Verifying new routes ==="
php artisan route:list | grep -i "disputes.assign\|cc-permissions"
