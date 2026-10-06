<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

class SeoController extends Controller
{
    public function robots()
    {
        $txt = "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /api\n\nSitemap: " . url('/sitemap.xml') . "\n";
        return response($txt, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap()
    {
        $names = ['welcome', 'about', 'faq', 'safety', 'news.index', 'gigs.index', 'marketplace.index', 'pulse.index', 'register'];
        $urls = [];
        foreach ($names as $n) {
            if (Route::has($n)) {
                $urls[] = route($n);
            }
        }
        $now = now()->toAtomString();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach (array_unique($urls) as $u) {
            $xml .= '  <url><loc>' . e($u) . '</loc><lastmod>' . $now . '</lastmod></url>' . "\n";
        }
        $xml .= '</urlset>' . "\n";
        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
