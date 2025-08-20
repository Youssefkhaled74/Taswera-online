<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $branches = [
            ['name' => 'Main Branch', 'is_active' => true],
            ['name' => 'Downtown Branch', 'is_active' => true],
            ['name' => 'Uptown Branch', 'is_active' => false],
            ['name' => 'Westside Branch', 'is_active' => true],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }
    }
}
