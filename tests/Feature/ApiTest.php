<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Owner;
use App\Models\Animal;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_owner()
    {
        $response = $this->postJson('/api/owner', [
            'nombre' => 'Juan',
            'apellido' => 'Perez'
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('datos.name', 'Juan')
            ->assertJsonPath('datos.surname', 'Perez');

        $this->assertDatabaseHas('owners', ['name' => 'Juan', 'surname' => 'Perez']);
    }

    public function test_create_animal()
    {
        $owner = Owner::create(['name' => 'Juan', 'surname' => 'Perez']);

        $response = $this->postJson('/api/animal', [
            'nombre' => 'Firulais',
            'tipo' => 'perro',
            'peso' => 10.5,
            'owner_id' => $owner->id
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('datos.nombre', 'Firulais')
            ->assertJsonPath('datos.tipo', 'perro');

        $this->assertDatabaseHas('animals', ['nombre' => 'Firulais', 'peso' => 10.5]);
    }

    public function test_delete_owner_cascade()
    {
        $owner = Owner::create(['name' => 'Juan', 'surname' => 'Perez']);
        $animal = Animal::create([
            'nombre' => 'Firulais',
            'tipo' => 'perro',
            'peso' => 10.5,
            'owner_id' => $owner->id
        ]);

        $this->assertDatabaseHas('owners', ['id' => $owner->id]);
        $this->assertDatabaseHas('animals', ['id' => $animal->id]);

        $response = $this->deleteJson("/api/owner/{$owner->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('owners', ['id' => $owner->id]);
        $this->assertDatabaseMissing('animals', ['id' => $animal->id]);
    }

    public function test_validation_animal_type()
    {
        $owner = Owner::create(['name' => 'Juan', 'surname' => 'Perez']);

        $response = $this->postJson('/api/animal', [
            'nombre' => 'Firulais',
            'tipo' => 'elefante', // Invalid
            'peso' => 10.5,
            'owner_id' => $owner->id
        ]);

        $response->assertStatus(400);
    }

    public function test_update_owner()
    {
        $owner = Owner::create(['name' => 'Juan', 'surname' => 'Perez']);

        $response = $this->putJson("/api/owner/{$owner->id}", [
            'nombre' => 'Juan Update',
            'apellido' => 'Perez Update'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('datos_actualizados.name', 'Juan Update')
            ->assertJsonPath('datos_actualizados.surname', 'Perez Update');

        $this->assertDatabaseHas('owners', ['name' => 'Juan Update']);
    }

    public function test_update_animal()
    {
        $owner = Owner::create(['name' => 'Juan', 'surname' => 'Perez']);
        $animal = Animal::create([
            'nombre' => 'Firulais',
            'tipo' => 'perro',
            'peso' => 10.5,
            'owner_id' => $owner->id
        ]);

        $response = $this->putJson("/api/animal/{$animal->id}", [
            'peso' => 12.5,
            'nombre' => 'Firulais Update'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('datos_actualizados.peso', 12.5)
            ->assertJsonPath('datos_actualizados.nombre', 'Firulais Update');

        $this->assertDatabaseHas('animals', ['peso' => 12.5, 'nombre' => 'Firulais Update']);
    }

    public function test_get_owners()
    {
        Owner::create(['name' => 'Juan', 'surname' => 'Perez']);
        Owner::create(['name' => 'Maria', 'surname' => 'Lopez']);

        $response = $this->getJson('/api/owners');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_get_animals()
    {
        $owner = Owner::create(['name' => 'Juan', 'surname' => 'Perez']);
        Animal::create(['nombre' => 'Firulais', 'tipo' => 'perro', 'peso' => 10, 'owner_id' => $owner->id]);

        $response = $this->getJson('/api/animals');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
