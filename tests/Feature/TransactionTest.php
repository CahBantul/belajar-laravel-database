<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('DELETE FROM categories');
    }

    public function testTransactionSuccess()
    {
        DB::transaction(function(){
            DB::insert('INSERT INTO categories(id, name, description, created_at) values (:id, :name, :description, :created_at)', [
                "id" => "GADGET", 
                "name" => "Gadget", 
                "description" => "Gadget Category", 
                "created_at" => "2020-10-10 10:10:10"
            ]);
            DB::insert('INSERT INTO categories(id, name, description, created_at) values (:id, :name, :description, :created_at)', [
                "id" => "FOOD", 
                "name" => "Food", 
                "description" => "Food Category", 
                "created_at" => "2020-10-10 10:10:10"
            ]);
        });

        $result = DB::select('SELECT * FROM categories');
        $this->assertCount(2, $result);
    }

    public function testTransactionFailed()
    {
        try {
            DB::transaction(function(){
                DB::insert('INSERT INTO categories(id, name, description, created_at) values (:id, :name, :description, :created_at)', [
                    "id" => "GADGET", 
                    "name" => "Gadget", 
                    "description" => "Gadget Category", 
                    "created_at" => "2020-10-10 10:10:10"
                ]);
    
                DB::insert('INSERT INTO categories(id, name, description, created_at) values (:id, :name, :description, :created_at)', [
                    "id" => "GADGET", 
                    "name" => "Gadget", 
                    "description" => "Gadget Category", 
                    "created_at" => "2020-10-10 10:10:10"
                ]);
            });
        } catch (\Illuminate\Database\QueryException $th) {
            //expected
        }


        $result = DB::select('SELECT * FROM categories');
        $this->assertCount(0, $result);
    }

    public function testMAnualTransactionSuccess()
    {
        try {
            DB::beginTransaction();
            DB::insert('INSERT INTO categories(id, name, description, created_at) values (:id, :name, :description, :created_at)', [
                "id" => "GADGET", 
                "name" => "Gadget", 
                "description" => "Gadget Category", 
                "created_at" => "2020-10-10 10:10:10"
            ]);

            DB::insert('INSERT INTO categories(id, name, description, created_at) values (:id, :name, :description, :created_at)', [
                "id" => "FOOD", 
                "name" => "Food", 
                "description" => "Food Category", 
                "created_at" => "2020-10-10 10:10:10"
            ]);
            DB::commit();
        } catch (\Illuminate\Database\QueryException $th) {
            DB::rollBack();
        }


        $result = DB::select('SELECT * FROM categories');
        $this->assertCount(2, $result);
    }

    public function testMAnualTransactionFailed()
    {
        try {
            DB::beginTransaction();
            DB::insert('INSERT INTO categories(id, name, description, created_at) values (:id, :name, :description, :created_at)', [
                "id" => "GADGET", 
                "name" => "Gadget", 
                "description" => "Gadget Category", 
                "created_at" => "2020-10-10 10:10:10"
            ]);

            DB::insert('INSERT INTO categories(id, name, description, created_at) values (:id, :name, :description, :created_at)', [
                "id" => "GADGET", 
                "name" => "Food", 
                "description" => "Food Category", 
                "created_at" => "2020-10-10 10:10:10"
            ]);
            DB::commit();
        } catch (\Illuminate\Database\QueryException $th) {
            DB::rollBack();
        }


        $result = DB::select('SELECT * FROM categories');
        $this->assertCount(0, $result);
    }
}
