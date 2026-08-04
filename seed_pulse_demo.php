\App\Models\News::create([
    'title' => 'Textile Study No. 12',
    'body' => 'Woven paper and thread, photographed under studio light for the spring materials show. Third piece in a series about repetition and hands.',
    'category' => 'Design Dept.',
    'type' => 'ad',
    'image_url' => 'https://picsum.photos/seed/pulse1/800/1200',
    'published_at' => now(),
]);

\App\Models\News::create([
    'title' => 'Concrete Bloom',
    'body' => 'A short loop built from hand-drawn frames, made for the stairwell projection at Hall B. Stop by after dark to see it full scale.',
    'category' => 'Film Society',
    'type' => 'ad',
    'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
    'published_at' => now()->subMinutes(5),
]);

\App\Models\News::create([
    'title' => 'Library open till 2am for midterms',
    'body' => 'Extended hours start Monday through the end of exam week. Third floor quiet zone requires ID badge after 11pm.',
    'category' => 'Campus',
    'type' => 'news',
    'image_url' => 'https://picsum.photos/seed/pulse2/800/1200',
    'published_at' => now()->subMinutes(10),
]);

\App\Models\News::create([
    'title' => 'Hall B Stairwell Mural',
    'body' => 'Two weeks, four cans a day, one very patient facilities office. Come sign the wall before it gets sealed next month.',
    'category' => 'Fine Arts',
    'type' => 'ad',
    'image_url' => 'https://picsum.photos/seed/pulse3/800/1200',
    'published_at' => now()->subMinutes(15),
]);

\App\Models\News::create([
    'title' => 'Solar car team qualifies for nationals',
    'body' => "The engineering team's third rebuild clocked the fastest qualifying lap in program history this weekend.",
    'category' => 'Campus',
    'type' => 'news',
    'image_url' => 'https://picsum.photos/seed/pulse4/800/1200',
    'published_at' => now()->subMinutes(20),
]);

echo "Seeded. Total posts now: " . \App\Models\News::count() . PHP_EOL;
