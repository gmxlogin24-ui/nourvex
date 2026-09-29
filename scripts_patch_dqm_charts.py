import re

path = "resources/views/partials/dashboard-minimal.blade.php"
with open(path, "r", encoding="utf-8") as f:
    content = f.read()

patches = []

# 1. Money Flow card -> wave chart
old_flow = '''<div class="dqm-card dqm-flow">                                               <div class="dqm-card-head"><span>Money Flow</span><span class="dqm-flow-net">₦{{ number_format($netFlow ?? 0) }} net</span></div>                   <div class="dqm-flow-track"><div class="dqm-flow-fill" style="width: {{ ($totalIn ?? 0) > 0 ? min(100, round((($netFlow ?? 0) / $totalIn) * 100)) : 0 }}%;"></div></div>                 <div class="dqm-flow-meta"><span>In: ₦{{ number_format($totalIn ?? 0) }}</span><span>Out: ₦{{ number_format($totalOut ?? 0) }}</span></div>
            </div>'''

new_flow = '''<div class="dqm-card dqm-flow dqm-flow-wave">
                <div class="dqm-flow-top">
                    <span class="dqm-flow-pill dqm-flow-pill-in"><i data-lucide="arrow-down" class="w-3 h-3"></i> In ₦{{ number_format($totalIn ?? 0) }}</span>
                    <span class="dqm-flow-pill dqm-flow-pill-out"><i data-lucide="arrow-up" class="w-3 h-3"></i> Out ₦{{ number_format($totalOut ?? 0) }}</span>
                </div>
                <div class="dqm-card-head"><span>Money Flow</span><span class="dqm-flow-net">₦{{ number_format($netFlow ?? 0) }} net</span></div>
                <div class="dqm-wave-wrap">
                    @php
                        $dqmWaveSeed = max(1, (int) (($netFlow ?? 0) + ($totalIn ?? 0) + ($totalOut ?? 0)));
                        $dqmWavePts = [];
                        for ($i = 0; $i <= 6; $i++) {
                            $n = sin($i * 1.3 + ($dqmWaveSeed % 7)) * 20 + cos($i * 0.7) * 12;
                            $dqmWavePts[] = round(50 - $n, 1);
                        }
                        $dqmWaveLine = '';
                        foreach ($dqmWavePts as $i => $y) {
                            $x = $i * 50;
                            $dqmWaveLine .= ($i === 0 ? "M{$x},{$y} " : "L{$x},{$y} ");
                        }
                        $dqmWaveFillPath = "M0,100 L0,{$dqmWavePts[0]} ";
                        foreach ($dqmWavePts as $i => $y) {
                            if ($i === 0) continue;
                            $x = $i * 50;
                            $dqmWaveFillPath .= "L{$x},{$y} ";
                        }
                        $dqmWaveFillPath .= "L300,100 Z";
                    @endphp
                    <svg class="dqm-wave-svg" viewBox="0 0 300 100" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="dqmWaveFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#7c5cf0" stop-opacity="0.45"/>
                                <stop offset="100%" stop-color="#7c5cf0" stop-opacity="0"/>
                            </linearGradient>
                            <linearGradient id="dqmWaveStroke" x1="0" y1="0" x2="1" y2="0">
                                <stop offset="0%" stop-color="#7c5cf0"/>
                                <stop offset="100%" stop-color="#22c55e"/>
                            </linearGradient>
                        </defs>
                        <path d="{{ $dqmWaveFillPath }}" fill="url(#dqmWaveFill)"></path>
                        <path d="{{ $dqmWaveLine }}" fill="none" stroke="url(#dqmWaveStroke)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        <circle cx="{{ 3 * 50 }}" cy="{{ $dqmWavePts[3] }}" r="4" fill="#fff"></circle>
                    </svg>
                </div>
                <div class="dqm-flow-meta"><span>In: ₦{{ number_format($totalIn ?? 0) }}</span><span>Out: ₦{{ number_format($totalOut ?? 0) }}</span></div>
            </div>'''

patches.append(("Money Flow wave chart", old_flow, new_flow))

# 2. Active Gigs sparkline
patches.append(("Active Gigs sparkline",
    '<span class="dqm-arow-v"><span class="dqm-countup" data-value="{{ $myOpenGigs ?? 0 }}">0</span> <small>this week</small></span>',
    '<span class="dqm-arow-v"><span class="dqm-countup" data-value="{{ $myOpenGigs ?? 0 }}">0</span> <small>this week</small></span>@include(\'partials.dqm-sparkline\', [\'seed\' => ($myOpenGigs ?? 0) + 2, \'color\' => \'#22c55e\'])'
))

# 3. Marketplace sparkline
patches.append(("Marketplace sparkline",
    '<span class="dqm-arow-v"><span class="dqm-countup" data-value="{{ $myListings ?? 0 }}">0</span> <small>listings</small></span>',
    '<span class="dqm-arow-v"><span class="dqm-countup" data-value="{{ $myListings ?? 0 }}">0</span> <small>listings</small></span>@include(\'partials.dqm-sparkline\', [\'seed\' => ($myListings ?? 0) + 5, \'color\' => \'#7c5cf0\'])'
))

# 4. Insert CSS right before closing </div> of dqm-page + trailing script (append before final </div> of page, safest: before the last gsap script closing tag's preceding </div> is risky; instead append style right after opening <div class="dqm-page">)
old_open = '<div class="dqm-page">'
new_open = '''<div class="dqm-page">
    <style>
    .dqm-flow-wave{padding:16px}
    .dqm-flow-top{display:flex;gap:8px;margin-bottom:10px}
    .dqm-flow-pill{display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;padding:4px 10px;border-radius:999px;background:rgba(0,0,0,.05);color:rgba(0,0,0,.6)}
    .dqm-flow-pill-in{color:#16a34a}
    .dqm-flow-pill-out{color:#ea580c}
    .dqm-wave-wrap{width:100%;height:80px;margin:8px 0 4px}
    .dqm-wave-svg{width:100%;height:100%;display:block}
    .dqm-arow-v{display:flex;align-items:center;gap:8px}
    .dqm-sparkline{width:60px;height:22px;display:block;opacity:.85}
    </style>'''

patches.append(("CSS block", old_open, new_open))

for name, old, new in patches:
    if old in content:
        content = content.replace(old, new, 1)
        print(f"[OK] {name}")
    else:
        print(f"[MISS] {name} -- not found, skipped.")

with open(path, "w", encoding="utf-8") as f:
    f.write(content)
