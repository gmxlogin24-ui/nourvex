@php($__all = \App\Models\InvestmentProject::latest()->get())
<div class="inv-glass inv-section">
  <a class="inv-btn small" href="{{ route('admin.kyc.index') }}">ID checks waiting</a>
  <a class="inv-btn small ghost" href="{{ route('admin.investment.audit') }}">Activity log</a>
  <h2 style="margin-top:12px">Due diligence and milestones</h2>
  @forelse($__all as $__p)
    <div class="inv-row">
      <span>{{ $__p->title }} <small class="inv-muted">{{ str_replace('_', ' ', $__p->status) }}</small></span>
      <a class="inv-status" href="{{ route('admin.investment.diligence', $__p) }}">Open</a>
    </div>
  @empty
    <p class="inv-muted">No projects yet.</p>
  @endforelse
</div>
