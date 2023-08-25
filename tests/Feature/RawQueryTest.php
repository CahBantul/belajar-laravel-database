<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RawQueryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('DELETE FROM categories');
    }

    public function testCrud()
    {
        DB::insert('INSERT INTO categories(id, name, description, created_at) values (?, ?, ?, ?)', [
            "GADGET", "Gadget", "Gadget Category", "2020-10-10 10:10:10"
        ]);

        $result = DB::select('SELECT * FROM categories WHERE id = ?', ['GADGET']);
        $this->assertCount(1, $result);
        $this->assertEquals("GADGET", $result[0]->id);
    }

    public function testNamedBinding()
    {
        DB::insert('INSERT INTO categories(id, name, description, created_at) values (:id, :name, :description, :created_at)', [
            "id" => "GADGET", 
            "name" => "Gadget", 
            "description" => "Gadget Category", 
            "created_at" => "2020-10-10 10:10:10"
        ]);

        $result = DB::select('SELECT * FROM categories WHERE id = :id', [ 'id' => 'GADGET']);
        $this->assertCount(1, $result);
        $this->assertEquals("GADGET", $result[0]->id);
    }
}
