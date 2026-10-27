(function(){ if(document.querySelector(".gx"))return;
var SEL='a,p,span,h1,h2,h3,h4,h5,h6,li,b,strong,small,label,button,em,td,th,i';
var st=document.createElement('style');
st.textContent='.ac-on-dark{color:#fff!important}.ac-on-light{color:var(--ac-ink,#0F1115)!important}';
document.head.appendChild(st);
function parse(c){var m=c&&c.match(/[\d.]+/g);return m?{r:+m[0],g:+m[1],b:+m[2],a:m[3]===undefined?1:+m[3]}:null}
function lum(c){function f(v){v/=255;return v<=.03928?v/12.92:Math.pow((v+.055)/1.055,2.4)}return .2126*f(c.r)+.7152*f(c.g)+.0722*f(c.b)}
function ratio(a,b){var x=lum(a),y=lum(b),h=Math.max(x,y),l=Math.min(x,y);return (h+.05)/(l+.05)}
function bgOf(el){
  var t=el.closest&&el.closest('[data-tone]');
  if(t)return t.getAttribute('data-tone')==='dark'?{r:15,g:17,b:21,a:1}:{r:255,g:255,b:255,a:1};
  var layers=[];
  for(var n=el;n&&n.nodeType===1;n=n.parentElement){
    var cs=getComputedStyle(n);
    if(cs.backgroundImage!=='none')return null;
    var c=parse(cs.backgroundColor);
    if(c&&c.a>0){layers.push(c);if(c.a>=.99)break}
  }
  var base={r:255,g:255,b:255};
  for(var i=layers.length-1;i>=0;i--){var l=layers[i];base={r:l.r*l.a+base.r*(1-l.a),g:l.g*l.a+base.g*(1-l.a),b:l.b*l.a+base.b*(1-l.a)}}
  base.a=1;return base;
}
function isFixed(el){for(var n=el;n&&n.nodeType===1;n=n.parentElement){var p=getComputedStyle(n).position;if(p==='fixed'||p==='sticky')return n}return null}
function behind(el){
  var f=isFixed(el);
  if(!f)return bgOf(el);
  var r=el.getBoundingClientRect();
  var list=document.elementsFromPoint(r.left+r.width/2,r.top+r.height/2);
  for(var i=0;i<list.length;i++){if(!f.contains(list[i]))return bgOf(list[i])}
  return bgOf(el);
}
function hasText(el){for(var n=el.firstChild;n;n=n.nextSibling){if(n.nodeType===3&&n.nodeValue.trim())return true}return el.tagName==='I'&&el.querySelector('svg')}
function run(){
  var els=document.querySelectorAll(SEL),k=0;
  (function chunk(){
    for(var n=0;n<150&&k<els.length;n++,k++){
      var el=els[k];
      if(!hasText(el)||(el.closest&&el.closest('.dg-card,.dg-hlh,.dg-stage-row,.gx')))continue;
      el.classList.remove('ac-on-dark','ac-on-light');
      var r=el.getBoundingClientRect();
      if(r.width===0||r.bottom<-200||r.top>innerHeight+200)continue;
      var cs=getComputedStyle(el);if(cs.visibility==='hidden'||cs.display==='none')continue;
      var fg=parse(cs.color),bg=behind(el);
      if(!fg||!bg)continue;
      if(ratio(fg,bg)<3)el.classList.add(lum(bg)<.4?'ac-on-dark':'ac-on-light');
    }
    if(k<els.length)requestAnimationFrame(chunk);
  })();
}
var tm;function soon(){clearTimeout(tm);tm=setTimeout(run,120)}
addEventListener('load',run);addEventListener('scroll',soon,{passive:true});addEventListener('resize',soon);
new MutationObserver(soon).observe(document.body,{childList:true,subtree:true});
})();
