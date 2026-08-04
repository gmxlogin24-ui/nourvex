document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.sponsor-stack').forEach(function (stack) {
    var cards = Array.from(stack.querySelectorAll('.sponsor-card'));
    if (cards.length === 0) return;

    var stackClasses = ['front', 'stack-1', 'stack-2', 'stack-3'];
    var order = cards.map(function (_, i) { return i; }); // order[0] = index of current front card

    function applyStack() {
      cards.forEach(function (card) {
        card.classList.remove('front', 'stack-1', 'stack-2', 'stack-3', 'stack-hidden', 'falling');
      });
      order.forEach(function (cardIndex, pos) {
        var card = cards[cardIndex];
        card.classList.add(pos < stackClasses.length ? stackClasses[pos] : 'stack-hidden');
      });
    }

    applyStack();
    if (cards.length < 2) return; // single card, nothing to rotate

    setInterval(function () {
      var frontCard = cards[order[0]];
      frontCard.classList.add('falling');

      setTimeout(function () {
        order.push(order.shift());
        applyStack();
      }, 700); // matches .falling transition duration
    }, 3000);
  });
});
