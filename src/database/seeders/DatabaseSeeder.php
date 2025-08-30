<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Building, Floor, Area};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $areaTypes = ['desks', 'meeting', 'common', 'lounge', 'cafeteria'];
        $buildingNames = ['HQ', 'Branch A', 'Branch B', 'Tech Park', 'Innovation Hub'];

        foreach ($buildingNames as $index => $bName) {
            $building = Building::create([
                'name' => $bName,
                'code' => strtoupper(str_replace(' ', '', $bName)),
                'address' => 'Address of ' . $bName . ', Dhaka',
            ]);

            for ($floorNum = 0; $floorNum < 3; $floorNum++) {
                $floor = $building->floors()->create([
                    'name' => $floorNum === 0 ? 'Ground' : "Floor {$floorNum}",
                    'number' => $floorNum,
                ]);

                $numAreas = rand(4, 5);
                $areas = [];
                for ($i = 1; $i <= $numAreas; $i++) {
                    $areas[] = [
                        'name' => "Area {$i} - {$floor->name}",
                        'type' => $areaTypes[array_rand($areaTypes)],
                        'capacity' => rand(5, 50),
                    ];
                }
                $floor->areas()->createMany($areas);
            }
        }
    }
}
