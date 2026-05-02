<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\AgencyStatus;
use Illuminate\Support\Str;

class AgencyStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name_translations' => [
                    ['locale' => 'en', 'value' => 'Approved'],
                    ['locale' => 'ar', 'value' => 'موافق عليه'],
                ],
                'color_code' => '#10B981',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name_translations' => [
                    ['locale' => 'en', 'value' => 'Pending Review'],
                    ['locale' => 'ar', 'value' => 'قيد المراجعة'],
                ],
                'color_code' => '#F59E0B',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name_translations' => [
                    ['locale' => 'en', 'value' => 'Rejected'],
                    ['locale' => 'ar', 'value' => 'مرفوضة'],
                ],
                'color_code' => '#EF4444',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name_translations' => [
                    ['locale' => 'en', 'value' => 'Suspended'],
                    ['locale' => 'ar', 'value' => 'معلقة'],
                ],
                'color_code' => '#8B5CF6',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name_translations' => [
                    ['locale' => 'en', 'value' => 'Archived'],
                    ['locale' => 'ar', 'value' => 'مؤرشفة'],
                ],
                'color_code' => '#6B7280',
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($statuses as $status) {
            AgencyStatus::create([
                'id' => (string) Str::ulid(),
                ...$status,
            ]);
        }
    }
}
