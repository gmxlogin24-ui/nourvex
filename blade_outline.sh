#!/bin/bash
FILE="$1"
[ -f "$FILE" ] || { echo "File not found: $FILE"; exit 1; }

grep -n -E '@(if|elseif|else|endif|php|endphp)\b' "$FILE" | sed -E 's/^([0-9]+):[[:space:]]*/\1: /'
