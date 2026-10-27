<?php

namespace Database\Seeders;

use App\Models\Gig;
use App\Models\User;
use Illuminate\Database\Seeder;

class GigDemoSeeder extends Seeder
{
    public function run(): void
    {
        $poster = User::orderBy('id')->skip(1)->first() ?: User::orderBy('id')->first();
        if (! $poster) { return; }
        $rows = [
            ['Type and bind my 40-page project', 'Typing & Printing', 'I have a handwritten project of about 40 pages that needs to be typed in Word, formatted with a cover page and table of contents, printed in black and white on A4 and spiral bound. I will send clear photos of every page.', 6000, [['Basic', 6000, 'Typing and formatting only, soft copy'], ['Standard', 9000, 'Typing, printing and spiral binding'], ['Premium', 13000, 'Everything plus proofreading and 2 printed copies']], 'remote', null, ['pages' => '40', 'color' => 'Black and white', 'paper_size' => 'A4', 'binding' => 'Spiral'], 3],
            ['Maths tutor for GST 111 exam prep', 'Tutoring', 'Looking for someone who can teach me the full GST 111 outline in time for exams. I struggle with sets and logic, so I need patient explanations and past questions solved step by step.', 4000, [['1 hour', 4000, 'One focused session'], ['3 hours', 10500, 'Three sessions over one week'], ['Full prep', 25000, 'Ten hours plus a mock test']], 'onsite', 'Library or any quiet spot on campus', ['subject' => 'GST 111 Mathematics', 'level' => '100 level', 'format' => 'In-person'], 5],
            ['Braiding for Saturday, medium knotless', 'Hair & Beauty', 'I want medium knotless braids, mid-back length. I will bring the hair. Please send photos of your previous work before you accept.', 12000, null, 'onsite', 'My hostel room, Hostel B', ['service' => 'Knotless braids', 'where' => 'Your place'], 4],
            ['Fix cracked phone screen, Tecno Spark', 'Repairs', 'My Tecno Spark 8 screen cracked and the touch still works. I need a replacement screen fitted today if possible. I can pay for the screen separately.', 8500, null, 'pickup', 'Main gate', ['item' => 'Phone screen', 'brand_model' => 'Tecno Spark 8'], 2],
            ['Wash and iron 20 items', 'Laundry', 'A mix of shirts, trousers and bedsheets that need washing, ironing and folding. I can drop off and pick up within two days.', 5000, [['Wash and fold', 5000, 'Washed and folded'], ['Wash and iron', 7500, 'Washed, ironed and folded']], 'pickup', 'Hostel A', ['item_count' => '20', 'service' => 'Wash and fold'], 4],
            ['Pick up my package from the park', 'Errands & Delivery', 'A parcel is waiting at the motor park and I cannot leave my lab. I need someone to collect it and bring it to my hostel before 6 pm.', 2500, null, 'onsite', 'Motor park to Hostel C', ['pickup' => 'Motor park', 'dropoff' => 'Hostel C', 'item' => 'Small parcel', 'item_cost_covered_by' => 'Included in gig amount'], 1],
            ['Photos for my birthday shoot', 'Photography', 'I need a photographer for a 1-hour birthday shoot around campus with about 20 edited photos delivered within two days.', 15000, [['Mini', 15000, '1 hour, 15 edited photos'], ['Standard', 25000, '2 hours, 30 edited photos'], ['Premium', 40000, '3 hours, 50 photos and a short reel']], 'onsite', 'Around campus', ['shoot_date' => 'Next Saturday', 'shoot_location' => 'Campus gardens'], 7],
            ['Physics tutor for 200 level mechanics', 'Tutoring', 'I need weekly help with 200 level mechanics: kinematics and Newton laws problem solving. Looking for someone who has done the course.', 6000, [['Weekly', 6000, 'One session each week'], ['Twice weekly', 11000, 'Two sessions each week']], 'remote', null, ['subject' => 'Physics (Mechanics)', 'level' => '200 level', 'format' => 'Online'], 6],
        ];
        foreach ($rows as $r) {
            if (Gig::where('title', $r[0])->exists()) { continue; }
            $pk = $r[4] ? array_map(fn ($p) => ['name' => $p[0], 'price' => $p[1], 'description' => $p[2]], $r[4]) : null;
            Gig::create([
                'title' => $r[0], 'category' => $r[1], 'description' => $r[2], 'amount' => $pk ? min(array_column($pk, 'price')) : $r[3],
                'escrowed_amount' => 0, 'user_id' => $poster->id, 'status' => 'open', 'packages' => $pk,
                'location_type' => $r[5], 'location' => $r[6], 'details' => $r[7], 'deadline' => now()->addDays($r[8])->toDateString(),
                'whatsapp' => 'DEMO',
            ]);
        }
    }
}
