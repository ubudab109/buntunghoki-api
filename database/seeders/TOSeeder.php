<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class TOSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            'member_id' => 11,
            'game_type' => 'PG SOFT - SLOT',
            'bet'       => 1200,
            'win'       => 1300,
            'result'    => 100,
            'status'    => 1,
            'created_at' => Date::now(),
            'updated_at' => Date::now(),
        ];

        for ($i = 0; $i < 100; $i++) {
            DB::table('turnover_members')->insert($data);
        }
    }
}
