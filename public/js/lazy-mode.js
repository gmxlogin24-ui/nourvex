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
