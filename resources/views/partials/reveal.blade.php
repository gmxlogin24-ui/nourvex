@php
  $rvKey   = $rvKey   ?? 'default';
  $rvTitle = $rvTitle ?? 'Secret offer is coming';
  $rvSub   = $rvSub   ?? 'Tap to see what you got';
  $rvPrize = $rvPrize ?? '₦500 bonus';
@endphp
<link rel="stylesheet" href="{{ asset('css/reveal.css') }}?v={{ time() }}">
<div class="rv" id="rvOv" hidden>
  <div class="rv-box">
    <h2 class="rv-t" id="rvT">{{ $rvTitle }}</h2>
    <p class="rv-s" id="rvS">{{ $rvSub }}</p>
    <div class="rv-gift" id="rvG">₦???</div>
    <button type="button" class="rv-btn" id="rvB">Press to reveal</button>
    <button type="button" class="rv-x" id="rvX">Close</button>
  </div>
</div>
<script>
(function(){
  var k='rv_'+@json($rvKey), ov=document.getElementById('rvOv');
  try{ if(sessionStorage.getItem(k)) return; }catch(e){}
  ov.hidden=false;
  var g=document.getElementById('rvG'),b=document.getElementById('rvB'),done=false;
  function close(){ ov.hidden=true; try{sessionStorage.setItem(k,'1');}catch(e){} }
  document.getElementById('rvX').onclick=close;
  b.onclick=function(){
    if(done){ close(); return; }
    g.classList.add('shake');
    setTimeout(function(){
      g.classList.remove('shake');
      g.textContent=@json($rvPrize); g.style.fontSize='28px';
      document.getElementById('rvT').textContent='You got it!';
      document.getElementById('rvS').textContent='';
      b.textContent='Awesome'; done=true;
    },1000);
  };
})();
</script>
