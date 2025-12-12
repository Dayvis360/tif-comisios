<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ProvinciaTest extends TestCase
{

    //No existen provincias
    public function test_example(): void
    {   
        $this->assertTrue(true);
    }

    protected $provincia;

    protected function setUp(): void
    {
        $this->provincia = new Provincia();
    }

    public function testCanCreateProvincia()
    {
        $this->provincia->nombre = 'Buenos Aires';
        $this->assertEquals('Buenos Aires', $this->provincia->nombre);
    }

    public function testProvinciaHasAttributes()
    {
        $this->assertClassHasAttribute('nombre', Provincia::class);
        $this->assertClassHasAttribute('id', Provincia::class);
    }

    public function testProvinciaCanBeSaved()
    {
        $this->provincia->nombre = 'Córdoba';
        $this->provincia->save();
        $this->assertDatabaseHas('provincias', ['nombre' => 'Córdoba']);
    }

    public function testProvinciaCanBeUpdated()
    {
        $this->provincia->nombre = 'Santa Fe';
        $this->provincia->save();
        $this->provincia->nombre = 'Santa Fe Actualizada';
        $this->provincia->save();
        $this->assertDatabaseHas('provincias', ['nombre' => 'Santa Fe Actualizada']);
    }

    public function testProvinciaCanBeDeleted()
    {
        $this->provincia->nombre = 'La Pampa';
        $this->provincia->save();
        $this->provincia->delete();
        $this->assertDeleted($this->provincia);
    }
}
