#!/bin/bash
FILE="$1"
[ -f "$FILE" ] || { echo "File not found: $FILE"; exit 1; }

awk '
{
  line = $0
  opens = gsub(/@if\(|@if /, "&", line)
  closes = gsub(/@endif/, "&", line)
  if (opens > 0) { depth++; print NR": +@if  depth="depth }
  if (closes > 0) {
    print NR": -@endif depth="depth
    depth--
    if (depth < 0) print "   ^^^ STRAY @endif at line "NR" (no open @if to match)"
  }
}
END { print "=== Final depth: "depth" (should be 0; positive = unclosed @if remaining) ===" }
' "$FILE"
