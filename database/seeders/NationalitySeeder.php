<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NationalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nationalities = [
            // Philippines (most common)
            ['name' => 'Filipino', 'code' => 'PHL', 'is_active' => true],
            ['name' => 'Philippine', 'code' => 'PHL', 'is_active' => true],
            
            // Asian Countries
            ['name' => 'Chinese', 'code' => 'CHN', 'is_active' => true],
            ['name' => 'Japanese', 'code' => 'JPN', 'is_active' => true],
            ['name' => 'Korean', 'code' => 'KOR', 'is_active' => true],
            ['name' => 'Indian', 'code' => 'IND', 'is_active' => true],
            ['name' => 'Indonesian', 'code' => 'IDN', 'is_active' => true],
            ['name' => 'Malaysian', 'code' => 'MYS', 'is_active' => true],
            ['name' => 'Singaporean', 'code' => 'SGP', 'is_active' => true],
            ['name' => 'Thai', 'code' => 'THA', 'is_active' => true],
            ['name' => 'Vietnamese', 'code' => 'VNM', 'is_active' => true],
            ['name' => 'Cambodian', 'code' => 'KHM', 'is_active' => true],
            ['name' => 'Laotian', 'code' => 'LAO', 'is_active' => true],
            ['name' => 'Myanmar', 'code' => 'MMR', 'is_active' => true],
            ['name' => 'Bangladeshi', 'code' => 'BGD', 'is_active' => true],
            ['name' => 'Pakistani', 'code' => 'PAK', 'is_active' => true],
            ['name' => 'Sri Lankan', 'code' => 'LKA', 'is_active' => true],
            ['name' => 'Nepalese', 'code' => 'NPL', 'is_active' => true],
            ['name' => 'Bhutanese', 'code' => 'BTN', 'is_active' => true],
            ['name' => 'Maldives', 'code' => 'MDV', 'is_active' => true],
            
            // Middle East
            ['name' => 'Saudi Arabian', 'code' => 'SAU', 'is_active' => true],
            ['name' => 'Emirati', 'code' => 'ARE', 'is_active' => true],
            ['name' => 'Qatari', 'code' => 'QAT', 'is_active' => true],
            ['name' => 'Kuwaiti', 'code' => 'KWT', 'is_active' => true],
            ['name' => 'Omani', 'code' => 'OMN', 'is_active' => true],
            ['name' => 'Bahraini', 'code' => 'BHR', 'is_active' => true],
            ['name' => 'Iranian', 'code' => 'IRN', 'is_active' => true],
            ['name' => 'Iraqi', 'code' => 'IRQ', 'is_active' => true],
            ['name' => 'Israeli', 'code' => 'ISR', 'is_active' => true],
            ['name' => 'Palestinian', 'code' => 'PSE', 'is_active' => true],
            ['name' => 'Jordanian', 'code' => 'JOR', 'is_active' => true],
            ['name' => 'Lebanese', 'code' => 'LBN', 'is_active' => true],
            ['name' => 'Syrian', 'code' => 'SYR', 'is_active' => true],
            ['name' => 'Turkish', 'code' => 'TUR', 'is_active' => true],
            
            // European Countries
            ['name' => 'American', 'code' => 'USA', 'is_active' => true],
            ['name' => 'British', 'code' => 'GBR', 'is_active' => true],
            ['name' => 'Canadian', 'code' => 'CAN', 'is_active' => true],
            ['name' => 'Australian', 'code' => 'AUS', 'is_active' => true],
            ['name' => 'New Zealander', 'code' => 'NZL', 'is_active' => true],
            ['name' => 'German', 'code' => 'DEU', 'is_active' => true],
            ['name' => 'French', 'code' => 'FRA', 'is_active' => true],
            ['name' => 'Italian', 'code' => 'ITA', 'is_active' => true],
            ['name' => 'Spanish', 'code' => 'ESP', 'is_active' => true],
            ['name' => 'Portuguese', 'code' => 'PRT', 'is_active' => true],
            ['name' => 'Dutch', 'code' => 'NLD', 'is_active' => true],
            ['name' => 'Belgian', 'code' => 'BEL', 'is_active' => true],
            ['name' => 'Swiss', 'code' => 'CHE', 'is_active' => true],
            ['name' => 'Austrian', 'code' => 'AUT', 'is_active' => true],
            ['name' => 'Swedish', 'code' => 'SWE', 'is_active' => true],
            ['name' => 'Norwegian', 'code' => 'NOR', 'is_active' => true],
            ['name' => 'Danish', 'code' => 'DNK', 'is_active' => true],
            ['name' => 'Finnish', 'code' => 'FIN', 'is_active' => true],
            ['name' => 'Polish', 'code' => 'POL', 'is_active' => true],
            ['name' => 'Russian', 'code' => 'RUS', 'is_active' => true],
            ['name' => 'Greek', 'code' => 'GRC', 'is_active' => true],
            ['name' => 'Irish', 'code' => 'IRL', 'is_active' => true],
            
            // Latin American Countries
            ['name' => 'Mexican', 'code' => 'MEX', 'is_active' => true],
            ['name' => 'Brazilian', 'code' => 'BRA', 'is_active' => true],
            ['name' => 'Argentine', 'code' => 'ARG', 'is_active' => true],
            ['name' => 'Chilean', 'code' => 'CHL', 'is_active' => true],
            ['name' => 'Colombian', 'code' => 'COL', 'is_active' => true],
            ['name' => 'Peruvian', 'code' => 'PER', 'is_active' => true],
            ['name' => 'Venezuelan', 'code' => 'VEN', 'is_active' => true],
            ['name' => 'Ecuadorian', 'code' => 'ECU', 'is_active' => true],
            ['name' => 'Guatemalan', 'code' => 'GTM', 'is_active' => true],
            ['name' => 'Cuban', 'code' => 'CUB', 'is_active' => true],
            
            // African Countries
            ['name' => 'Nigerian', 'code' => 'NGA', 'is_active' => true],
            ['name' => 'South African', 'code' => 'ZAF', 'is_active' => true],
            ['name' => 'Egyptian', 'code' => 'EGY', 'is_active' => true],
            ['name' => 'Kenyan', 'code' => 'KEN', 'is_active' => true],
            ['name' => 'Ethiopian', 'code' => 'ETH', 'is_active' => true],
            ['name' => 'Ghanaian', 'code' => 'GHA', 'is_active' => true],
            
            // Other
            ['name' => 'Stateless', 'code' => null, 'is_active' => true],
            ['name' => 'Unknown', 'code' => null, 'is_active' => true],
        ];

        foreach ($nationalities as $nationality) {
            DB::table('nationalities')->updateOrInsert(
                ['name' => $nationality['name']],
                [
                    'code' => $nationality['code'],
                    'is_active' => $nationality['is_active'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
