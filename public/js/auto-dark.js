(function(){
if(document.querySelector(".gx"))return;
if(window.__nvAutoDark)return;window.__nvAutoDark=1;
var R=document.documentElement,STATE=new WeakMap(),TOUCHED=[],ON=false,G=0;
var SKIP={SCRIPT:1,STYLE:1,LINK:1,META:1,HEAD:1,NOSCRIPT:1,TEMPLATE:1,IMG:1,VIDEO:1,CANVAS:1,IFRAME:1,PICTURE:1,SVG:1};
var TEXT='#f5f5f5',LINE='rgba(255,255,255,0.18)',BLACK='#000';
var SHEEN_CSS='.nv-sheen{position:relative;isolation:isolate}'+
'.nv-sheen::before{content:"";position:absolute;inset:0;border-radius:inherit;pointer-events:none;z-index:-1;'+
'background:linear-gradient(160deg,rgba(0,0,0,.78) 0%,rgba(0,0,0,.42) 55%,rgba(0,0,0,.85) 100%)}';

function isDark(){var c=R.classList;if(c.contains('nv-dark'))return true;if(c.contains('nv-light'))return false;return R.getAttribute('data-theme')==='dark'}
function parse(s){if(!s||s.indexOf('color(')===0)return null;var m=s.match(/[\d.]+/g);if(!m||m.length<3)return null;var a=m.length>3?+m[3]:1;if(a>1)a/=100;return{r:+m[0],g:+m[1],b:+m[2],a:a}}
function lum(c){function f(v){v/=255;return v<=.03928?v/12.92:Math.pow((v+.055)/1.055,2.4)}return .2126*f(c.r)+.7152*f(c.g)+.0722*f(c.b)}
function sat(c){return Math.max(c.r,c.g,c.b)-Math.min(c.r,c.g,c.b)}
function mustBlack(c){if(!c||c.a<.5)return false;if(sat(c)>=60&&lum(c)>.25)return false;return lum(c)>.003}
function set(el,p,v){var st=STATE.get(el);if(!st){st={};STATE.set(el,st);TOUCHED.push(el)}if(!(p in st))st[p]=[el.style.getPropertyValue(p),el.style.getPropertyPriority(p)];el.style.setProperty(p,v,'important')}
function effBg(el){for(var e=el;e;e=e.parentElement){var c=parse(getComputedStyle(e).backgroundColor);if(c&&c.a>=.5)return c}return{r:0,g:0,b:0,a:1}}
function isCard(el,cs){
  var w=el.offsetWidth,h=el.offsetHeight,vw=window.innerWidth;
  if(w<80||h<80||w>vw*.96)return false;
  if(/card|box|panel|tile|item|gig|stat|sheet|modal|hustle|highlight|slide/i.test(String(el.className||'')))return true;
  return parseFloat(cs.borderTopLeftRadius)>=8;
}

function fix(el){
  if(SKIP[el.tagName]||el.namespaceURI!=='http://www.w3.org/1999/xhtml')return;
  var cs=getComputedStyle(el),bg=parse(cs.backgroundColor);
  if(mustBlack(bg))set(el,'background-color',BLACK);

  var bi=cs.backgroundImage;
  if(bi&&bi!=='none'&&bi.indexOf('gradient')>-1){
    var nb=bi.replace(/rgba?\([^)]*\)/g,function(m){var c=parse(m);return mustBlack(c)?'rgba(0,0,0,'+c.a+')':m});
    if(nb!==bi)set(el,'background-image',nb);
  }

  if(isCard(el,cs))el.classList.add('nv-sheen');

  var fg=parse(cs.color);
  if(fg&&fg.a>0&&lum(fg)<.25&&lum(effBg(el))<.2)set(el,'color',TEXT);

  ['Top','Right','Bottom','Left'].forEach(function(s){
    if(!(parseFloat(cs['border'+s+'Width'])>0)||cs['border'+s+'Style']==='none')return;
    var bc=parse(cs['border'+s+'Color']);
    if(bc&&bc.a>0&&lum(bc)>.003)set(el,'border-'+s.toLowerCase()+'-color',LINE);
  });
}

function run(list){
  var g=G,i=0;
  (function step(){
    if(g!==G)return;
    var end=Math.min(i+400,list.length);
    for(;i<end;i++){if(list[i].isConnected)fix(list[i])}
    if(i<list.length)requestAnimationFrame(step);
  })();
}

function injectCss(){
  if(document.getElementById('nv-sheen-css'))return;
  var s=document.createElement('style');s.id='nv-sheen-css';s.textContent=SHEEN_CSS;document.head.appendChild(s);
}

function start(){
  ON=true;
  injectCss();
  set(R,'color-scheme','dark');
  set(R,'background-color',BLACK);
  set(R,'color',TEXT);
  run([R].concat(Array.prototype.slice.call(document.getElementsByTagName('*'))));
}

function stop(){
  ON=false;G++;
  document.querySelectorAll('.nv-sheen').forEach(function(el){el.classList.remove('nv-sheen')});
  TOUCHED.forEach(function(el){
    var st=STATE.get(el);if(!st)return;
    for(var p in st){var v=st[p];if(v[0])el.style.setProperty(p,v[0],v[1]);else el.style.removeProperty(p)}
    STATE.delete(el);
  });
  TOUCHED.length=0;
}

var pend=new Set(),tm;
function flush(){if(!ON)return;var list=Array.from(pend);pend.clear();run(list)}
new MutationObserver(function(ms){
  if(!ON)return;
  ms.forEach(function(m){
    if(m.type==='childList'){
      m.addedNodes.forEach(function(n){
        if(n.nodeType!==1)return;
        pend.add(n);
        n.querySelectorAll&&n.querySelectorAll('*').forEach(function(x){pend.add(x)});
      });
    }else pend.add(m.target);
  });
  clearTimeout(tm);tm=setTimeout(flush,60);
}).observe(document.body,{childList:true,subtree:true,attributes:true,attributeFilter:['class']});

new MutationObserver(function(){
  var d=isDark();if(d===ON)return;
  if(d)start();else stop();
}).observe(R,{attributes:true,attributeFilter:['class','data-theme']});

if(isDark())start();
})();
