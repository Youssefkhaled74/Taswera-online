<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\SyncJob;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class SyncJobsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $branches = Branch::pluck('id')->toArray();
        $statuses = ['pending', 'completed', 'failed'];
        $shiftNames = ['Morning Shift', 'Afternoon Shift', 'Night Shift'];

        for ($i = 0; $i < 20; $i++) {
            SyncJob::create([
                'branch_id' => $faker->randomElement($branches),
                'employeeName' => $faker->name,
                'pay_amount' => $faker->randomFloat(2, 50, 1000),
                'orderprefixcode' => 'ORD' . $faker->unique()->numberBetween(100, 999),
                'status' => $faker->randomElement($statuses),
                'shift_name' => $faker->randomElement($shiftNames),
                'orderphone' => $faker->phoneNumber,
                'number_of_photos' => $faker->numberBetween(1, 10),
            ]);
        }
    }
}
