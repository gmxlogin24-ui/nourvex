document.addEventListener('DOMContentLoaded', function () {
  const items = window.__campusNewsItems || [];
  if (!items.length) return;

  const stack = document.createElement('div');
  stack.className = 'news-toast-stack';
  document.body.appendChild(stack);

  let i = 0;
  function showNext() {
    const item = items[i % items.length];
    i++;
    const toast = document.createElement('div');
    toast.className = 'news-toast';
    toast.innerHTML = `
      <div class="news-toast-icon"><i data-lucide="newspaper" class="w-4 h-4"></i></div>
      <div style="flex:1;">
        <div class="news-toast-title">${item.title}</div>
        <div class="news-toast-body">${item.category ?? 'Campus'} · ${item.time ?? 'now'}</div>
      </div>
      <span class="news-toast-close">&times;</span>
    `;
    stack.appendChild(toast);
    if (window.lucide) lucide.createIcons();
    requestAnimationFrame(() => toast.classList.add('show'));

    const remove = () => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 500);
    };
    toast.querySelector('.news-toast-close').addEventListener('click', remove);
    setTimeout(remove, 7000);
  }

  showNext();
  setInterval(showNext, 10000);
});
