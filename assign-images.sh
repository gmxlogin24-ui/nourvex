#!/bin/bash
INBOX="public/images/inbox"
DEST="public/images"

PAGES=("default" "welcome" "dashboard" "gigs" "marketplace" "news" "referrals" "tier" "posts" "leaderboard" "services" "profile")

if [ ! -d "$INBOX" ] || [ -z "$(ls -A $INBOX 2>/dev/null)" ]; then
    echo "No files in $INBOX. Drop pictures there first."
    exit 0
fi

for file in "$INBOX"/*; do
    [ -f "$file" ] || continue
    filename=$(basename "$file")
    ext="${filename##*.}"

    echo ""
    echo "File: $filename"
    echo "Which page is this for?"
    for i in "${!PAGES[@]}"; do
        echo "  $i) ${PAGES[$i]}"
    done
    echo "  s) skip this file"
    read -p "Choice: " choice

    if [ "$choice" == "s" ]; then
        echo "Skipped $filename"
        continue
    fi

    if ! [[ "$choice" =~ ^[0-9]+$ ]] || [ "$choice" -ge "${#PAGES[@]}" ]; then
        echo "Invalid choice, skipping $filename"
        continue
    fi

    target="$DEST/bg-${PAGES[$choice]}.jpg"
    cp "$file" "$target"
    rm "$file"
    echo "-> Saved as $target"
done

echo ""
echo "Done. Run: php artisan view:clear"
