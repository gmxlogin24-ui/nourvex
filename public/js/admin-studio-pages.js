(function () {
  function init() {
    var pb = document.querySelector('.admin-page-body');
    if (!pb || document.body.classList.contains('ax-lock') || pb.querySelector('.stu,.ov') || pb.classList.contains('sp-on')) return;
    pb.classList.add('sp-on');
    document.body.classList.add('sp-stones');
    var bar = document.createElement('div'); bar.className = 'sp-bar';
    var d = new Date().toLocaleDateString(undefined, { day: '2-digit', month: 'short', year: 'numeric' });
    bar.innerHTML = '<a href="/admin">Studio</a><a href="/admin/map">Map</a><span>' + d + '</span><button type="button" id="spS">Search pages</button>';
    pb.insertBefore(bar, pb.firstChild);
    document.getElementById('spS').addEventListener('click', function () { var k = document.getElementById('axK'); if (k) k.click(); });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
