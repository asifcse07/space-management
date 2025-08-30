<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Building,Floor,Area};

class DatabaseSeeder extends Seeder {
    public function run(): void {
        $b = Building::create(['name'=>'HQ','code'=>'HQ','address'=>'Dhaka']);
        $f1 = $b->floors()->create(['name'=>'Ground','number'=>0]);
        $f2 = $b->floors()->create(['name'=>'First','number'=>1]);
        $f1->areas()->createMany([
            ['name'=>'Lobby','type'=>'common','capacity'=>20],
            ['name'=>'West Workstations','type'=>'desks','capacity'=>40],
        ]);
        $f2->areas()->createMany([
            ['name'=>'Conference A','type'=>'meeting','capacity'=>8],
            ['name'=>'Conference B','type'=>'meeting','capacity'=>12],
        ]);
    }
}
