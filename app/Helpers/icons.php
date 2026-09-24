<?php

if (!function_exists('ph_icon')) {
    function ph_icon(?string $lucideName, string $fallback = 'circle'): string
    {
        static $map = [
            'activity' => 'activity', 'align-left' => 'text-align-left',
            'arrow-down-circle' => 'arrow-circle-down', 'arrow-down-to-line' => 'arrow-line-down',
            'arrow-left' => 'arrow-left', 'arrow-left-right' => 'arrows-left-right',
            'arrow-right' => 'arrow-right', 'arrow-up-circle' => 'arrow-circle-up',
            'award' => 'medal', 'badge-check' => 'seal-check', 'banknote' => 'money',
            'bell' => 'bell', 'bell-off' => 'bell-slash', 'briefcase' => 'briefcase',
            'building-2' => 'buildings', 'check' => 'check', 'check-circle' => 'check-circle',
            'chevron-down' => 'caret-down', 'chevron-left' => 'caret-left', 'chevron-right' => 'caret-right',
            'circle-check' => 'check-circle', 'circle-dashed' => 'circle-dashed', 'circle-x' => 'x-circle',
            'clock' => 'clock', 'coins' => 'coins', 'credit-card' => 'credit-card', 'crown' => 'crown',
            'eye' => 'eye', 'file-text' => 'file-text', 'flame' => 'flame', 'gem' => 'diamond',
            'gift' => 'gift', 'hash' => 'hash', 'headset' => 'headset', 'heart' => 'heart',
            'home' => 'house', 'image' => 'image', 'key' => 'key', 'landmark' => 'bank',
            'layout-dashboard' => 'squares-four', 'lightbulb' => 'lightbulb', 'line-chart' => 'chart-line',
            'list' => 'list', 'list-checks' => 'list-checks', 'lock' => 'lock', 'log-in' => 'sign-in',
            'log-out' => 'sign-out', 'mail' => 'envelope', 'map-pin' => 'map-pin', 'medal' => 'medal',
            'megaphone' => 'megaphone', 'message-circle' => 'chat-circle', 'message-square' => 'chat-square',
            'minus' => 'minus', 'moon-star' => 'moon-stars', 'newspaper' => 'newspaper',
            'package' => 'package', 'package-check' => 'package', 'paintbrush' => 'paint-brush',
            'palette' => 'palette', 'paperclip' => 'paperclip', 'party-popper' => 'confetti',
            'pencil' => 'pencil-simple', 'phone' => 'phone', 'plug-zap' => 'plug', 'plus' => 'plus',
            'rotate-ccw' => 'arrow-counter-clockwise', 'save' => 'floppy-disk', 'scale' => 'scales',
            'search' => 'magnifying-glass', 'send' => 'paper-plane-tilt', 'shield' => 'shield',
            'shield-alert' => 'shield-warning', 'shield-check' => 'shield-check', 'shopping-bag' => 'shopping-bag',
            'shopping-cart' => 'shopping-cart', 'smartphone' => 'device-mobile', 'sparkles' => 'sparkle',
            'sprout' => 'plant', 'star' => 'star', 'store' => 'storefront', 'sun-moon' => 'sun',
            'tag' => 'tag', 'toggle-left' => 'toggle-left', 'trash-2' => 'trash', 'trending-up' => 'trend-up',
            'trophy' => 'trophy', 'truck' => 'truck', 'tv' => 'television', 'user' => 'user',
            'user-circle' => 'user-circle', 'user-plus' => 'user-plus', 'users' => 'users',
            'wallet' => 'wallet', 'wifi' => 'wifi-high', 'x' => 'x', 'zap' => 'lightning',
            'gamepad-2' => 'game-controller', 'sparkle' => 'sparkle',
        ];
        return $map[$lucideName] ?? $fallback;
    }
}
