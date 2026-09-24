<?php

namespace App\Http\Controllers;

use App\Models\GigDispute;
use App\Models\ListingDispute;
use App\Models\ListingOrder;
use App\Models\Flag;
use App\Models\GigMessage;
use App\Models\ListingMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerCareController extends Controller
{
    public function index()
    {
        return view('customer-care.index', [
            'openGigDisputes' => GigDispute::where('status', 'open')->count(),
            'openListingDisputes' => ListingDispute::where('status', 'open')->count(),
            'pendingDeliveries' => ListingOrder::whereNull('delivery_confirmed_at')->where('status', 'paid')->count(),
            'openFlags' => Flag::where('status', 'open')->count(),
        ]);
    }

    // ===== TRACKING / DELIVERY =====
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

    public function confirmDelivery(Request $r, ListingOrder $order)
    {
        $validated = $r->validate(['tracking_id' => 'required|string']);

        abort_unless($order->tracking_id && $order->tracking_id === $validated['tracking_id'], 422, 'Tracking ID does not match this order.');

        $order->update([
            'delivery_confirmed_at' => now(),
            'delivery_confirmed_by' => Auth::id(),
            'status' => 'delivered',
        ]);

        return back()->with('status', 'Delivery confirmed for order #' . $order->id);
    }

    // ===== DISPUTES =====
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

    public function addGigDisputeNote(Request $r, GigDispute $dispute)
    {
        $validated = $r->validate(['note' => 'required|string|max:2000']);
        $existing = $dispute->cc_notes ? $dispute->cc_notes . "\n\n" : '';
        $dispute->update(['cc_notes' => $existing . '[' . now()->format('M j, g:ia') . ' - ' . Auth::user()->name . '] ' . $validated['note']]);
        return back()->with('status', 'Note added.');
    }

    public function addListingDisputeNote(Request $r, ListingDispute $dispute)
    {
        $validated = $r->validate(['note' => 'required|string|max:2000']);
        $existing = $dispute->cc_notes ? $dispute->cc_notes . "\n\n" : '';
        $dispute->update(['cc_notes' => $existing . '[' . now()->format('M j, g:ia') . ' - ' . Auth::user()->name . '] ' . $validated['note']]);
        return back()->with('status', 'Note added.');
    }

    public function escalateGigDispute(GigDispute $dispute)
    {
        $dispute->update(['escalated' => true]);
        return back()->with('status', 'Escalated to admin.');
    }

    public function escalateListingDispute(ListingDispute $dispute)
    {
        $dispute->update(['escalated' => true]);
        return back()->with('status', 'Escalated to admin.');
    }

    // ===== MESSAGING (join existing threads) =====
    public function gigThread(\App\Models\Gig $gig)
    {
        $conversation = \App\Models\Conversation::where('type', 'business')
            ->where('subject_type', \App\Models\Gig::class)
            ->where('subject_id', $gig->id)
            ->first();
        $messages = $conversation ? $conversation->messages()->with('sender')->get() : collect();
        return view('customer-care.gig-thread', ['gig' => $gig, 'messages' => $messages]);
    }

    public function sendGigMessage(Request $r, \App\Models\Gig $gig)
    {
        $validated = $r->validate(['recipient_id' => 'required|exists:users,id', 'body' => 'required|string|max:2000']);

        $conversation = \App\Models\Conversation::where('type', 'business')
            ->where('subject_type', \App\Models\Gig::class)
            ->where('subject_id', $gig->id)
            ->first();
        if (!$conversation) {
            $conversation = \App\Models\Conversation::create([
                'type' => 'business',
                'subject_type' => \App\Models\Gig::class,
                'subject_id' => $gig->id,
            ]);
        }
        foreach ([$gig->user_id, $gig->accepted_by] as $uid) {
            if ($uid) {
                \App\Models\ConversationParticipant::firstOrCreate(['conversation_id' => $conversation->id, 'user_id' => $uid]);
            }
        }

        \App\Models\ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'message_type' => 'text',
            'is_system' => false,
            'body' => '[Customer Care] ' . $validated['body'],
        ]);
        $conversation->update(['last_message_at' => now()]);

        return back()->with('status', 'Message sent.');
    }

    public function listingThread(\App\Models\Listing $listing)
    {
        $messages = \App\Models\ConversationMessage::whereHas('conversation', function ($q) use ($listing) {
            $q->where('type', 'business')->where('subject_type', \App\Models\Listing::class)->where('subject_id', $listing->id);
        })->with('sender')->orderBy('created_at')->get();
        return view('customer-care.listing-thread', ['listing' => $listing, 'messages' => $messages]);
    }

    public function sendListingMessage(Request $r, \App\Models\Listing $listing)
    {
        $validated = $r->validate(['recipient_id' => 'required|exists:users,id', 'body' => 'required|string|max:2000']);
        $ids = collect([$listing->user_id, (int) $validated['recipient_id']])->unique()->sort()->values();
        $conversation = \App\Models\Conversation::where('type', 'business')
            ->where('subject_type', \App\Models\Listing::class)
            ->where('subject_id', $listing->id)
            ->get()
            ->first(function ($conv) use ($ids) {
                $convIds = $conv->participants()->pluck('user_id')->unique()->sort()->values();
                return $convIds->all() === $ids->all();
            });
        if (!$conversation) {
            $conversation = \App\Models\Conversation::create([
                'type' => 'business',
                'subject_type' => \App\Models\Listing::class,
                'subject_id' => $listing->id,
            ]);
            foreach ($ids as $uid) {
                \App\Models\ConversationParticipant::firstOrCreate(['conversation_id' => $conversation->id, 'user_id' => $uid]);
            }
        }
        \App\Models\ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'message_type' => 'text',
            'body' => '[Customer Care] ' . $validated['body'],
        ]);
        $conversation->update(['last_message_at' => now()]);
        return back()->with('status', 'Message sent.');
    }

    // ===== FLAGGING =====
    public function flag(Request $r)
    {
        $validated = $r->validate([
            'flaggable_type' => 'required|in:user,listing,gig',
            'flaggable_id' => 'required|integer',
            'reason' => 'required|string|max:1000',
        ]);

        $typeMap = ['user' => \App\Models\User::class, 'listing' => \App\Models\Listing::class, 'gig' => \App\Models\Gig::class];

        Flag::create([
            'flaggable_type' => $typeMap[$validated['flaggable_type']],
            'flaggable_id' => $validated['flaggable_id'],
            'reason' => $validated['reason'],
            'raised_by' => Auth::id(),
            'status' => 'open',
        ]);

        return back()->with('status', 'Flagged for admin review.');
    }

    // ===== READ-ONLY USER LOOKUP =====
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
}
