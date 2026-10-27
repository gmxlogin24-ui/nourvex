(function(){
  document.addEventListener('click',function(e){
    var b=e.target.closest&&e.target.closest('[data-theme-btn]');
    if(!b)return;
    e.preventDefault();
    var h=document.documentElement,dark=h.getAttribute('data-theme')==='dark',n=dark?'light':'dark';
    h.setAttribute('data-theme',n);
    try{localStorage.setItem('theme',n)}catch(x){}
    setTimeout(function(){window.dispatchEvent(new Event('scroll'))},60);
  });
})();
