@php $spCart = count(session('cart', [])); @endphp
<nav class="sp-tabs">
  <a href="{{ route('cart.index') }}" class="{{ ($active ?? '') === 'cart' ? 'on' : '' }}"><i data-lucide="shopping-cart"></i>Cart @if($spCart > 0)<em>{{ $spCart }}</em>@endif</a>
  <a href="{{ route('marketplace.orders') }}" class="{{ ($active ?? '') === 'orders' ? 'on' : '' }}"><i data-lucide="package"></i>Orders</a>
  <a href="{{ route('inbox.market') }}" class="{{ ($active ?? '') === 'inbox' ? 'on' : '' }}"><i data-lucide="message-circle"></i>Messages</a>
  <a href="{{ route('marketplace.favorites') }}" class="{{ ($active ?? '') === 'saved' ? 'on' : '' }}"><i data-lucide="heart"></i>Wishlist</a>
</nav>
