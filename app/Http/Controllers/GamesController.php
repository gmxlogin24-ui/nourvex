<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

class GamesController extends Controller
{
    public function index()
    {
        $games = array_map(function ($g) {
            unset($g['route'], $g['folder'], $g['embed']);
            return $g;
        }, $this->catalog());

        return view('games.index', ['games' => $games]);
    }

    public function play(string $slug)
    {
        $game = collect($this->catalog())->firstWhere('slug', $slug);
        abort_unless($game && $game['live'], 404);

        if (!empty($game['route'])) {
            return redirect($game['url']);
        }

        $local = !empty($game['folder']);
        $src = $local ? route('games.file', ['slug' => $game['folder'], 'path' => 'index.html']) : $game['embed'];

        return view('games.play', ['game' => $game, 'src' => $src, 'local' => $local]);
    }

    public function file(string $slug, ?string $path = null)
    {
        abort_unless(preg_match('/^[a-z0-9._-]+$/i', $slug) === 1, 404);
        $base = realpath(storage_path('gamefiles/' . $slug));
        abort_unless($base && is_dir($base), 404);

        $full = realpath($base . '/' . ltrim((string) ($path ?: 'index.html'), '/'));
        if ($full && is_dir($full)) {
            $full = realpath($full . '/index.html');
        }
        abort_unless($full && is_file($full) && str_starts_with($full, $base . DIRECTORY_SEPARATOR), 404);

        $types = [
            'html' => 'text/html; charset=utf-8', 'htm' => 'text/html; charset=utf-8',
            'js' => 'text/javascript; charset=utf-8', 'mjs' => 'text/javascript; charset=utf-8',
            'css' => 'text/css; charset=utf-8', 'json' => 'application/json', 'txt' => 'text/plain; charset=utf-8',
            'xml' => 'application/xml', 'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif', 'svg' => 'image/svg+xml', 'webp' => 'image/webp', 'ico' => 'image/x-icon',
            'mp3' => 'audio/mpeg', 'ogg' => 'audio/ogg', 'wav' => 'audio/wav', 'm4a' => 'audio/mp4',
            'woff' => 'font/woff', 'woff2' => 'font/woff2', 'ttf' => 'font/ttf', 'otf' => 'font/otf',
            'wasm' => 'application/wasm', 'mp4' => 'video/mp4', 'webm' => 'video/webm',
        ];
        $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
        $headers = [
            'Content-Type' => $types[$ext] ?? 'application/octet-stream',
            'Access-Control-Allow-Origin' => '*',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'public, max-age=3600',
        ];

        if ($ext === 'html' || $ext === 'htm') {
            $shim = '<script>(function(){try{window.localStorage.getItem("a")}catch(e){var s={},st={getItem:function(k){return Object.prototype.hasOwnProperty.call(s,k)?s[k]:null},setItem:function(k,v){s[k]=String(v)},removeItem:function(k){delete s[k]},clear:function(){s={}},key:function(i){return Object.keys(s)[i]||null}};Object.defineProperty(st,"length",{get:function(){return Object.keys(s).length}});try{Object.defineProperty(window,"localStorage",{value:st,configurable:true})}catch(x){}try{Object.defineProperty(window,"sessionStorage",{value:st,configurable:true})}catch(x){}}})();</script>';
            $html = file_get_contents($full);
            $count = 0;
            $html = preg_replace_callback('/<head[^>]*>/i', fn ($m) => $m[0] . $shim, $html, 1, $count);
            if (!$count) {
                $html = $shim . $html;
            }
            $headers['Content-Security-Policy'] = 'sandbox allow-scripts allow-pointer-lock allow-forms';
            return response($html, 200, $headers);
        }

        return response()->file($full, $headers);
    }

    private function catalog(): array
    {
        // Your own games go here. Use one of:
        //   'route'  => 'route.name'      a game page already in your site
        //   'embed'  => 'https://...'     a game link from a provider (https only)
        // Free games from GitHub are listed automatically from storage/gamefiles/catalog.json
        $list = [
            ['slug' => 'nagolf', 'title' => 'NaGolf', 'tagline' => 'Tee up and play NaGolf', 'genre' => 'Golf', 'icon' => 'flag', 'c1' => '#22c55e', 'c2' => '#052e1b', 'route' => 'golfvex.index'],
        ];
        $f = storage_path('gamefiles/catalog.json');
        if (is_file($f)) {
            $more = json_decode((string) file_get_contents($f), true);
            if (is_array($more)) {
                foreach ($more as $g) {
                    $list[] = $g;
                }
            }
        }

        $games = [];
        foreach ($list as $g) {
            $live = false;
            $url = null;
            if (!empty($g['route']) && Route::has($g['route'])) {
                $live = true;
                $url = route($g['route']);
            } elseif (!empty($g['folder']) && file_exists(storage_path('gamefiles/' . $g['folder'] . '/index.html'))) {
                $live = true;
                $url = route('games.play', $g['slug']);
            } elseif (!empty($g['embed']) && str_starts_with($g['embed'], 'https://')) {
                $live = true;
                $url = route('games.play', $g['slug']);
            }

            $img = null; $ip = null; $ie = null;
            foreach (['jpg', 'jpeg', 'png', 'webp', 'svg'] as $ext) {
                $p = public_path('images/games/' . $g['slug'] . '.' . $ext);
                if (file_exists($p)) {
                    $img = asset('images/games/' . $g['slug'] . '.' . $ext) . '?v=' . filemtime($p);
                    $ip = $p; $ie = $ext;
                    break;
                }
            }
            $w = 9999;
            if ($ip && $ie !== 'svg') {
                $inf = @getimagesize($ip);
                if ($inf) {
                    $w = (int) $inf[0];
                }
            }

            $g['live'] = $live;
            $g['url'] = $url;
            $g['badge'] = $live ? 'Live now' : 'Coming soon';
            $g['image_url'] = $img;
            $g['img_w'] = $w;
            $g['generated'] = ($ie === 'svg' && $g['slug'] !== '2048');
            $g['preview'] = (!empty($g['folder']) && $live) ? route('games.file', ['slug' => $g['folder'], 'path' => 'index.html']) : null;
            $g['credit'] = $g['credit'] ?? '';
            $games[] = $g;
        }
        usort($games, fn ($a, $b) => (int) $b['live'] <=> (int) $a['live']);

        return $games;
    }
}
