<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Batch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use App\Services\Qr\QrCodeService;
use App\Support\Access\AccessControl;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::query()->firstOrCreate(
            ['email' => 'admin@test'],
            [
                'name' => 'Super Admin',
                'phone' => '9000000001',
                'password' => Hash::make('password'),
                'status' => 'active',
                'approved_at' => now(),
                'email_verified_at' => now(),
            ],
        );
        $superAdmin->assignRole(AccessControl::ROLE_SUPER_ADMIN);

        // Blame stamps for everything created below attribute to the admin.
        Auth::login($superAdmin);

        $company = Company::query()->firstOrCreate(
            ['slug' => 'saint-globle'],
            [
                'name' => 'Saint Globle Industries',
                'legal_name' => 'Saint Globle Industries Pvt. Ltd.',
                'email' => 'contact@saintgloble.test',
                'phone' => '18001234567',
                'gstin' => '27ABCDE1234F1Z5',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'country' => 'India',
                'pincode' => '400001',
                'status' => 'active',
            ],
        );

        $superAdmin->forceFill(['company_id' => $company->id])->save();

        $companyAdmin = User::query()->firstOrCreate(
            ['email' => 'company@test'],
            [
                'name' => 'Company Manager',
                'phone' => '9000000002',
                'password' => Hash::make('password'),
                'company_id' => $company->id,
                'status' => 'active',
                'approved_at' => now(),
                'email_verified_at' => now(),
            ],
        );
        $companyAdmin->assignRole(AccessControl::ROLE_COMPANY);

        $brand = Brand::query()->firstOrCreate(
            ['company_id' => $company->id, 'slug' => 'saint-premium'],
            ['name' => 'Saint Premium', 'status' => 'active', 'description' => 'Premium building materials line.'],
        );

        $category = Category::query()->firstOrCreate(
            ['company_id' => $company->id, 'slug' => 'adhesives'],
            ['name' => 'Adhesives', 'status' => 'active', 'sort_order' => 1],
        );

        // Home-carousel slides. Images are uploaded from the admin panel, so
        // these seed without one and fall back to the brand gradient.
        $banners = [
            [
                'title' => 'Win Exciting Gifts on Scanning Products',
                'subtitle' => 'From Saint Globle',
                'button_label' => 'Avail Now',
                'link_url' => '/scan',
                'sort_order' => 1,
            ],
            [
                'title' => 'Earn Reward Points on Every Genuine Product',
                'subtitle' => 'Scan the QR label to collect vCash',
                'button_label' => 'Scan Now',
                'link_url' => '/scan',
                'sort_order' => 2,
            ],
            [
                'title' => 'Redeem Your vCash Straight to Your Wallet',
                'subtitle' => 'Fast payouts on approved requests',
                'button_label' => 'View Rewards',
                'link_url' => '/my/rewards',
                'sort_order' => 3,
            ],
        ];

        foreach ($banners as $data) {
            Banner::query()->firstOrCreate(
                ['company_id' => $company->id, 'title' => $data['title']],
                [...$data, 'status' => 'active'],
            );
        }

        $products = [
            ['name' => 'Saint Tile Adhesive 20kg', 'sku' => 'SG-TA-20', 'mrp' => 480.00, 'reward_points' => 25],
            ['name' => 'Saint Wall Putty 40kg', 'sku' => 'SG-WP-40', 'mrp' => 1050.00, 'reward_points' => 60],
            ['name' => 'Saint Waterproof Coating 5L', 'sku' => 'SG-WC-05', 'mrp' => 890.00, 'reward_points' => 45],
        ];

        $qrService = app(QrCodeService::class);

        foreach ($products as $data) {
            $product = Product::query()->firstOrCreate(
                ['company_id' => $company->id, 'sku' => $data['sku']],
                [
                    'brand_id' => $brand->id,
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'unit' => 'piece',
                    'mrp' => $data['mrp'],
                    'reward_points' => $data['reward_points'],
                    'status' => 'active',
                ],
            );

            $batch = Batch::query()->firstOrCreate(
                ['product_id' => $product->id, 'code' => 'B-'.strtoupper(Str::random(6))],
                [
                    'company_id' => $company->id,
                    'manufacture_date' => now()->subDays(10),
                    'expiry_date' => now()->addYears(2),
                    'quantity' => 10,
                    'status' => Batch::STATUS_DRAFT,
                ],
            );

            if ($batch->qrCodes()->count() === 0) {
                $qrService->generateForBatch($batch->refresh(), 10);
            }
        }

        Auth::logout();
    }
}
