set -e
FILES=$(grep -rl 'fa-' resources/views || true)
[ -z "$FILES" ] && { echo "No FontAwesome usage found."; exit 0; }

# Pass 1: <i class="fas fa-x mr-2"></i>  ->  <i data-lucide="x" class="inline w-4 h-4 mr-2"></i>
echo "$FILES" | xargs sed -i -E \
  's#<i class="fa[sbrl]?[[:space:]]+fa-([a-z0-9-]+)([^"]*)"></i>#<i data-lucide="\1" class="inline w-4 h-4\2"></i>#g'

# Pass 2: rename the icons whose Lucide name differs from FontAwesome
echo "$FILES" | xargs sed -i \
  -e 's/data-lucide="edit"/data-lucide="pencil"/g' \
  -e 's/data-lucide="trash"/data-lucide="trash-2"/g' \
  -e 's/data-lucide="trash-alt"/data-lucide="trash-2"/g' \
  -e 's/data-lucide="check-circle"/data-lucide="circle-check"/g' \
  -e 's/data-lucide="times-circle"/data-lucide="circle-x"/g' \
  -e 's/data-lucide="times"/data-lucide="x"/g' \
  -e 's/data-lucide="exclamation-circle"/data-lucide="circle-alert"/g' \
  -e 's/data-lucide="exclamation-triangle"/data-lucide="triangle-alert"/g' \
  -e 's/data-lucide="question"/data-lucide="circle-help"/g' \
  -e 's/data-lucide="info-circle"/data-lucide="info"/g' \
  -e 's/data-lucide="shield-alt"/data-lucide="shield-check"/g' \
  -e 's/data-lucide="icons"/data-lucide="sparkles"/g' \
  -e 's/data-lucide="tasks"/data-lucide="list-checks"/g' \
  -e 's/data-lucide="sort-numeric-up"/data-lucide="hash"/g' \
  -e 's/data-lucide="sort-numeric-down"/data-lucide="hash"/g' \
  -e 's/data-lucide="eye-slash"/data-lucide="eye-off"/g' \
  -e 's/data-lucide="sign-out-alt"/data-lucide="log-out"/g' \
  -e 's/data-lucide="sign-in-alt"/data-lucide="log-in"/g' \
  -e 's/data-lucide="tachometer-alt"/data-lucide="gauge"/g' \
  -e 's/data-lucide="money-bill-wave"/data-lucide="banknote"/g' \
  -e 's/data-lucide="wallet"/data-lucide="wallet"/g' \
  -e 's/data-lucide="coins"/data-lucide="coins"/g' \
  -e 's/data-lucide="fire"/data-lucide="flame"/g' \
  -e 's/data-lucide="user-friends"/data-lucide="users"/g' \
  -e 's/data-lucide="users-cog"/data-lucide="users"/g' \
  -e 's/data-lucide="shopping-cart"/data-lucide="shopping-cart"/g' \
  -e 's/data-lucide="briefcase"/data-lucide="briefcase"/g' \
  -e 's/data-lucide="comments"/data-lucide="messages-square"/g' \
  -e 's/data-lucide="comment"/data-lucide="message-circle"/g' \
  -e 's/data-lucide="bell"/data-lucide="bell"/g' \
  -e 's/data-lucide="cog"/data-lucide="settings"/g' \
  -e 's/data-lucide="cogs"/data-lucide="settings-2"/g' \
  -e 's/data-lucide="chart-line"/data-lucide="chart-line"/g' \
  -e 's/data-lucide="chart-bar"/data-lucide="chart-column"/g' \
  -e 's/data-lucide="history"/data-lucide="history"/g' \
  -e 's/data-lucide="gift"/data-lucide="gift"/g' \
  -e 's/data-lucide="medal"/data-lucide="medal"/g' \
  -e 's/data-lucide="crown"/data-lucide="crown"/g' \
  -e 's/data-lucide="bolt"/data-lucide="zap"/g' \
  -e 's/data-lucide="clock"/data-lucide="clock"/g' \
  -e 's/data-lucide="calendar-alt"/data-lucide="calendar-days"/g' \
  -e 's/data-lucide="arrow-left"/data-lucide="arrow-left"/g' \
  -e 's/data-lucide="paper-plane"/data-lucide="send"/g' \
  -e 's/data-lucide="mobile-alt"/data-lucide="smartphone"/g' \
  -e 's/data-lucide="tv"/data-lucide="tv"/g' \
  -e 's/data-lucide="lightbulb"/data-lucide="lightbulb"/g'

echo "Converted files:"
echo "$FILES"
echo
echo "Remaining FontAwesome references (review manually):"
grep -rn 'fa-' resources/views || echo "none"
