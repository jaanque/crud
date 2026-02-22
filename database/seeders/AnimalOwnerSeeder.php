<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Owner;
use App\Models\Animal;

class AnimalOwnerSeeder extends Seeder
{
    public function run()
    {
        for ($i = 0; $i < 10; $i++) {
            $owner = Owner::create([
                'name' => 'Owner ' . $i,
                'surname' => 'Surname ' . $i,
            ]);

            Animal::create([
                'nombre' => 'Animal ' . $i,
                'tipo' => 'Dog',
                'peso' => 10,
                'enfermedad' => 'None',
                'comentarios' => 'None',
                'owner_id' => $owner->id,
            ]);
        }
    }
}
