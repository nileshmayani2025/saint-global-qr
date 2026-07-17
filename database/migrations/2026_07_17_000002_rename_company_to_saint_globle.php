<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The brand is spelled "Saint Globle" (as on the logo), but the seeded company
 * row predates that and still reads "Saint Global". Rename in place — the row
 * owns every product, batch and QR code, so it can't be recreated.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('companies')->where('slug', 'saint-global')->update([
            'name' => 'Saint Globle Industries',
            'legal_name' => 'Saint Globle Industries Pvt. Ltd.',
            'email' => 'contact@saintgloble.test',
            'slug' => 'saint-globle',
        ]);
    }

    public function down(): void
    {
        DB::table('companies')->where('slug', 'saint-globle')->update([
            'name' => 'Saint Global Industries',
            'legal_name' => 'Saint Global Industries Pvt. Ltd.',
            'email' => 'contact@saintglobal.test',
            'slug' => 'saint-global',
        ]);
    }
};
