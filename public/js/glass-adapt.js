(function(){
  var Q=location.search,FORCE=/[?&]ga=/.test(Q),DEBUG=/[?&]ga=debug/.test(Q);
  var SEL='.nv-dock-fixed,.pb-bar,.ovm-bar,.dqm-topbar-fixed,[data-adapt]'+(FORCE?',.dg-glass,.glass-card':'');
  
  var TXT='a,span,i,b,p,small,em,strong,label,h1,h2,h3,h4';
  var NOT=':not(.nv-badge,.nv-badge *,.dqm-badge,.dqm-topbar-avatar *,.pb-av *,.dg-av *)';
  var st=document.createElement('style');
  st.textContent=
    '.ga-on-light{--ga-ink:#0F1115}'+
    '.ga-on-dark{--ga-ink:#fff}'+
    'html body :is(.ga-on-light,.ga-on-dark):not(#ga) :is('+TXT+')'+NOT+'{color:var(--ga-ink)!important;transition:color .25s}'+
    'html body :is(.ga-on-light,.ga-on-dark):not(#ga) svg'+NOT+'{color:var(--ga-ink)!important;stroke:var(--ga-ink)!important;transition:stroke .25s}'+
    'html body .ga-on-dark:not(#ga) .ovd-lbl{text-shadow:none!important}'+
    (DEBUG?'.ga-on-light{outline:2px solid #0f0}.ga-on-dark{outline:2px solid #f0f}':'');
  document.head.appendChild(st);

  var cv=document.createElement('canvas');cv.width=cv.height=1;
  var cx=cv.getContext('2d',{willReadFrequently:true}),CACHE={};
  function L(r,g,b){function f(v){v/=255;return v<=.03928?v/12.92:Math.pow((v+.055)/1.055,2.4)}return .2126*f(r)+.7152*f(g)+.0722*f(b)}
  function rgba(s){if(!s||s.indexOf('color(')===0)return null;var m=s.match(/[\d.]+/g);if(!m||m.length<3)return null;var a=m.length>3?+m[3]:1;if(a>1)a/=100;return{r:+m[0],g:+m[1],b:+m[2],a:a}}

  function pick(img,nw,nh,r,fit,x,y){
    if(!nw||!nh||!r.width||!r.height)return null;
    var u=x-r.left,v=y-r.top,sx,sy;
    if(fit==='cover'||fit==='contain'){
      var k=fit==='contain'?Math.min(r.width/nw,r.height/nh):Math.max(r.width/nw,r.height/nh);
      sx=(u-(r.width-nw*k)/2)/k;sy=(v-(r.height-nh*k)/2)/k;
    }else{sx=u/r.width*nw;sy=v/r.height*nh}
    if(sx<0||sy<0||sx>=nw||sy>=nh)return null;
    try{
      cx.clearRect(0,0,1,1);
      cx.drawImage(img,Math.max(0,sx-3),Math.max(0,sy-3),6,6,0,0,1,1);
      var d=cx.getImageData(0,0,1,1).data;
      if(d[3]<128)return null;
      return L(d[0],d[1],d[2]);
    }catch(e){return null}
  }
  function bgUrl(cs){var m=cs.backgroundImage.match(/url\(["']?([^"')]+)["']?\)/);return m&&m[1]}
  function load(u){
    if(u in CACHE)return CACHE[u];
    CACHE[u]=null;
    var im=new Image();im.crossOrigin='anonymous';
    im.onload=function(){CACHE[u]=im;go()};
    im.src=u;return null;
  }

  function behind(card,x,y){
    var list=document.elementsFromPoint(x,y);
    for(var i=0;i<list.length;i++){
      var el=list[i];if(card.contains(el))continue;
      var r=el.getBoundingClientRect(),cs=getComputedStyle(el),t=el.tagName,v=null;
      if(t==='IMG'&&el.complete&&el.naturalWidth)v=pick(el,el.naturalWidth,el.naturalHeight,r,cs.objectFit,x,y);
      else if(t==='VIDEO'&&el.readyState>=2&&el.videoWidth)v=pick(el,el.videoWidth,el.videoHeight,r,cs.objectFit,x,y);
      if(v!==null)return v;
      var u=bgUrl(cs);
      if(u){var im=load(u);if(im){v=pick(im,im.naturalWidth,im.naturalHeight,r,cs.backgroundSize==='contain'?'contain':'cover',x,y);if(v!==null)return v}}
      var bi=cs.backgroundImage;
      if(bi.indexOf('gradient')>-1){
        var cl=(bi.match(/rgba?\([^)]*\)/g)||[]).map(rgba).filter(function(c){return c&&c.a>.3});
        if(cl.length){var s=0;cl.forEach(function(c){s+=L(c.r,c.g,c.b)});return s/cl.length}
      }
      var c=rgba(cs.backgroundColor);
      if(c&&c.a>=.5)return L(c.r,c.g,c.b);
    }
    return null;
  }

  function run(){
    var cards=document.querySelectorAll(SEL),dk=document.documentElement.getAttribute('data-theme')==='dark';
    for(var i=0;i<cards.length;i++){
      var c=cards[i],r=c.getBoundingClientRect();
      if(r.width<20||r.bottom<0||r.top>innerHeight)continue;
      var s=0,n=0;
      [.25,.5,.75].forEach(function(fx){[.33,.66].forEach(function(fy){
        var x=r.left+r.width*fx,y=r.top+r.height*fy;
        if(x<0||y<0||x>=innerWidth||y>=innerHeight)return;
        var v=behind(c,x,y);if(v!==null){s+=v;n++}
      })});
      var lum=n?s/n:(dk?.02:.95);
      var cc=rgba(getComputedStyle(c).backgroundColor);
      if(cc&&cc.a>0)lum=lum*(1-cc.a)+L(cc.r,cc.g,cc.b)*cc.a;
      var cur=c.__ga;
      var light=cur==='l'?lum>.30:cur==='d'?lum>.40:lum>.35;
      var k=light?'l':'d';
      if(k!==cur){c.__ga=k;c.classList.toggle('ga-on-light',light);c.classList.toggle('ga-on-dark',!light)}
      if(DEBUG)c.setAttribute('data-ga-lum',lum.toFixed(2));
    }
  }

  var tm,t=0,busy=false;
  function go(){
    clearTimeout(tm);tm=setTimeout(run,140);
    var n=Date.now();
    if(!busy&&n-t>90){busy=true;requestAnimationFrame(function(){busy=false;t=Date.now();run()})}
  }
  addEventListener('scroll',go,{passive:true});
  addEventListener('resize',go);
  addEventListener('load',function(){go();setTimeout(go,800)});
  if(document.readyState==='complete')go();
})();
