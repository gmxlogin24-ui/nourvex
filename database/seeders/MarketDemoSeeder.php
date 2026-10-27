<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\ListingImage;
use Illuminate\Database\Seeder;

class MarketDemoSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            "Leather Crossbody Bag" => ["img" => "bag.jpg", "brand" => "Urban Craft", "old" => 26000, "colors" => "Brown, Black, Tan", "sizes" => null,
                "desc" => "A structured crossbody bag in soft, full-grain style leather with a roomy main compartment, a zipped inner pocket and an adjustable shoulder strap. It sits comfortably against the body, so it works for lectures, weekend outings and evenings out.\nThe hardware is brushed metal that will not tarnish, and the stitching is double-reinforced at every stress point. Wipe with a damp cloth to clean.",
                "hi" => "Genuine leather finish\nAdjustable, removable strap\nZipped inner pocket for cards and keys\nFits a 10-inch tablet and a water bottle",
                "box" => "Leather crossbody bag\nDust bag\nCare card",
                "specs" => "Material: Leather\nWidth: 26 cm\nHeight: 18 cm\nDepth: 8 cm\nStrap drop: 50 to 62 cm\nWeight: 650 g",
                "war" => "1 month", "ret" => "Returns accepted within 3 days if the item is faulty or not as described.", "neg" => 1, "stock" => 10],
            "Suede Chukka Boots" => ["img" => "boots.jpg", "brand" => "Trailmark", "old" => 41000, "colors" => "Tan, Chocolate, Grey", "sizes" => "40, 41, 42, 43, 44, 45",
                "desc" => "Classic ankle-high chukka boots in soft suede with a cushioned insole and a durable rubber outsole. They dress up with chinos and dress down with jeans.\nThe padded collar keeps your ankle comfortable from the first wear, and the lace-up front gives a secure, adjustable fit. Treat with a suede protector spray to keep them looking new.",
                "hi" => "Soft suede upper\nCushioned insole\nGrippy rubber outsole\nPadded ankle collar",
                "box" => "1 pair of boots\nSpare laces\nShoe bag",
                "specs" => "Upper: Suede\nSole: Rubber\nClosure: Lace-up\nHeight: Ankle\nFit: True to size",
                "war" => null, "ret" => "Exchange for another size within 3 days if unworn and in the original box.", "neg" => 1, "stock" => 10],
            "White Court Sneakers" => ["img" => "sneakers.jpg", "brand" => "Court Line", "old" => 30000, "colors" => "White, White/Green, Black", "sizes" => "39, 40, 41, 42, 43, 44",
                "desc" => "Clean, low-profile court sneakers with a smooth upper, a cushioned footbed and a flexible cupsole. Light enough for all-day wear and simple enough to go with almost anything.\nThe perforated toe box helps airflow, and the wipe-clean upper makes upkeep easy.",
                "hi" => "Wipe-clean upper\nCushioned footbed\nFlexible, non-slip cupsole\nBreathable perforated toe box",
                "box" => "1 pair of sneakers\nSpare laces\nOriginal box",
                "specs" => "Upper: Synthetic leather\nSole: Rubber cupsole\nClosure: Lace-up\nFit: True to size",
                "war" => null, "ret" => "Exchange for another size within 3 days if unworn and in the original box.", "neg" => 0, "stock" => 10],
            "Classic Steel Watch" => ["img" => "watch.jpg", "brand" => "Meridian", "old" => 60000, "colors" => "Silver, Black, Gold", "sizes" => null,
                "desc" => "A timeless stainless-steel watch with a scratch-resistant crystal, a date window and a luminous dial for night reading. The link bracelet is adjustable and ships with a sizing tool.\nWater resistant to 50 metres, so it handles splashes and hand-washing, but it is not meant for swimming or diving.",
                "hi" => "Stainless steel case and bracelet\nScratch-resistant crystal\nDate window\nLuminous hands\nWater resistant to 50 m",
                "box" => "Watch\nSizing tool\nGift box\nWarranty card",
                "specs" => "Case: 40 mm stainless steel\nMovement: Quartz\nWater resistance: 5 ATM\nBracelet: Adjustable link\nBattery life: about 2 years",
                "war" => "6 months", "ret" => "Returns accepted within 3 days if the watch is faulty or not as described.", "neg" => 1, "stock" => 10],
            "Wireless Headphones" => ["img" => "headphones.jpg", "brand" => "SoundWave", "old" => 52000, "colors" => "Black, White, Blue", "sizes" => null,
                "desc" => "Over-ear wireless headphones with deep bass, clear vocals and active noise reduction that cuts out hostel noise and generator hum. The ear cushions are memory foam, so long study sessions stay comfortable.\nFold flat into the included case. Connect to two devices at once, or plug in the cable when the battery runs low.",
                "hi" => "Active noise cancellation\nUp to 30 hours of playback\nBluetooth 5.3, connects to two devices\nBuilt-in microphone for calls\nFoldable with a hard carry case",
                "box" => "Headphones\nCarry case\nUSB-C charging cable\n3.5 mm audio cable\nUser guide",
                "specs" => "Type: Over-ear\nConnectivity: Bluetooth 5.3 and 3.5 mm\nBattery: 30 hours\nCharging: USB-C, about 2 hours\nWeight: 250 g",
                "war" => "3 months", "ret" => "Returns accepted within 3 days if faulty or not as described.", "neg" => 1, "stock" => 10],
            "Mirrorless Camera Kit" => ["img" => "camera.jpg", "brand" => "Lumix Pro", "old" => 340000, "colors" => "Black", "sizes" => null,
                "desc" => "A compact mirrorless camera kit for photography and video: a 24 MP sensor, fast autofocus, 4K video and a tilting touchscreen that makes vlogging and low-angle shots easy. Comes with a versatile zoom lens, so you can start shooting straight out of the box.\nWi-Fi and Bluetooth let you send photos to your phone in seconds.",
                "hi" => "24 MP sensor\n4K video recording\nTilting touchscreen\nWi-Fi and Bluetooth transfer\nKit zoom lens included",
                "box" => "Camera body\nKit lens\nBattery and charger\nShoulder strap\nBody and lens caps\nUSB cable",
                "specs" => "Sensor: 24 MP APS-C\nVideo: 4K at 30 fps\nScreen: 3 inch tilting touchscreen\nLens mount: Interchangeable\nBattery life: about 350 shots",
                "war" => "6 months", "ret" => "Returns accepted within 3 days if the camera is faulty. Test it in front of the seller before you confirm.", "neg" => 1, "stock" => 3],
            "Portable Bluetooth Speaker" => ["img" => "speaker.jpg", "brand" => "BoomBox", "old" => 28000, "colors" => "Black, Red, Teal", "sizes" => null,
                "desc" => "A rugged portable speaker with loud, balanced sound and punchy bass. Splash-proof, so it is safe by the pool and in the rain, with a long battery for a full day of music.\nPair two speakers together for stereo sound at your next hangout.",
                "hi" => "Splash-proof design\n12 hours of playtime\nPair two speakers for stereo\nBuilt-in microphone\nCarry strap included",
                "box" => "Speaker\nUSB-C cable\nCarry strap\nUser guide",
                "specs" => "Output: 20 W\nBattery: 12 hours\nWater resistance: IPX5\nConnectivity: Bluetooth 5.0\nCharging: USB-C",
                "war" => "3 months", "ret" => "Returns accepted within 3 days if faulty or not as described.", "neg" => 1, "stock" => 10],
            "Study Books Bundle" => ["img" => "books.jpg", "brand" => null, "old" => 14000, "colors" => null, "sizes" => null,
                "desc" => "A bundle of university study books in good, readable condition: clean pages, intact spines and only light pencil notes in a few margins. Ideal for first and second year students who want core texts without paying full price.\nMessage the seller to confirm exactly which titles are in the bundle before you buy.",
                "hi" => "Bundle price, cheaper than buying one by one\nClean pages and intact spines\nLight pencil notes only\nMeet on campus to inspect before paying",
                "box" => "Bundle of study books",
                "specs" => "Condition: Used, good\nFormat: Paperback and hardcover\nLanguage: English",
                "war" => null, "ret" => "Check the books at meetup. Returns are not accepted once you confirm receipt.", "neg" => 1, "stock" => 10],
        ];

        foreach ($data as $title => $d) {
            $l = Listing::where("title", $title)->first();
            if (! $l) { continue; }
            $img = file_exists(public_path("images/products/" . $d["img"])) ? "/images/products/" . $d["img"] : $l->image_url;
            $l->forceFill([
                "description" => $d["desc"], "brand" => $d["brand"], "highlights" => $d["hi"],
                "whats_in_box" => $d["box"], "warranty" => $d["war"], "return_policy" => $d["ret"],
                "negotiable" => $d["neg"], "old_price" => $d["old"], "colors" => $d["colors"],
                "sizes" => $d["sizes"], "specs" => $d["specs"], "image_url" => $img,
                "stock_quantity" => $d["stock"], "condition" => "New",
            ])->save();
            if ($img) {
                ListingImage::updateOrCreate(["listing_id" => $l->id, "sort_order" => 0], ["image_url" => $img]);
            }
        }
    }
}
