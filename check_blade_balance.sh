#!/bin/bash
# Usage: ./check_blade_balance.sh resources/views/dashboard2.blade.php
FILE="$1"

if [ -z "$FILE" ] || [ ! -f "$FILE" ]; then
  echo "File not found: $FILE"
  exit 1
fi

echo "=== Line-by-line @if / @endif tracking ==="
awk '
{
  line = $0
  opens = gsub(/@if\(|@if /, "&", line)
  closes = gsub(/@endif/, "&", line)
  if (opens > 0) { depth++; print NR": +@if -> depth="depth" | "$0 }
  if (closes > 0) { print NR": -@endif -> depth="depth" | "$0; depth-- }
  if (depth < 0) { print "!!! MISMATCH at line "NR" - extra @endif"; exit 1 }
}
END { print "Final depth (should be 0): "depth }
' "$FILE"

echo ""
echo "=== Raw counts ==="
echo "@if / @elseif count:   $(grep -c '@if\|@elseif' "$FILE")"
echo "@endif count:          $(grep -c '@endif' "$FILE")"
echo "@foreach count:        $(grep -c '@foreach' "$FILE")"
echo "@endforeach count:     $(grep -c '@endforeach' "$FILE")"
echo "@php count:            $(grep -c '@php' "$FILE")"
echo "@endphp count:         $(grep -c '@endphp' "$FILE")"

echo ""
echo "=== Context around line 124 ==="
sed -n '90,124p' "$FILE" | nl -ba -v90
