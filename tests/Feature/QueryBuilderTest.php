<?php

namespace Tests\Feature;

use Database\Seeders\CategorySeeder;
use Database\Seeders\CounterSeeder;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertNotNull;

class QueryBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('DELETE FROM products');
        DB::delete('DELETE FROM categories');
        DB::delete('DELETE FROM counters');
    }

    public function testInsert()
    {
        DB::table("categories")->insert([
            "id" => "GADGET",
            "name" => "Gadget"
        ]);
        DB::table("categories")->insert([
            "id" => "FOOD",
            "name" => "Food"
        ]);

        $result = DB::select('SELECT count(id) AS total FROM categories');
        $this->assertEquals(2, $result[0]->total);
    }

    public function testSelect()
    {
        $this->testInsert();

        $collection = DB::table("categories")->select(["id", "name"])->get();
        $this->assertNotNull($collection);

        $collection->each(fn ($item) => Log::info(json_encode($item)));
    }

    protected function insertCategories() 
    {
        $this->seed(CategorySeeder::class);
    }

    protected function insertManyCategories() 
    {
        for ($i=0; $i < 100; $i++) { 
            DB::table("categories")->insert([
                "id" => "CATEGORY-$i",
                "name" => "Category-$i",
                "created_at" => "2020-10-10 10:10:10"
            ]);
        }
    }

    public function testWhere()
    {
        $this->insertCategories();
        
        $collection = DB::table("categories")->where(function (Builder $builder) {
            $builder->where("id", "=", "LAPTOP");
            $builder->orWhere("id", "=", "FOOD");
        })->get();

        $this->assertCount(2, $collection);
        $collection->each(fn ($item) => info(json_encode($item)));
    }

    public function testWhereBetween()
    {
        $this->insertCategories();
        
        $collection = DB::table("categories")->whereBetween("created_at", ["2020-9-10 10:10:10", "2020-11-10 10:10:10"])->get();

        $this->assertCount(4, $collection);
        $collection->each(fn ($item) => info(json_encode($item)));
    }

    public function testWhereIn()
    {
        $this->insertCategories();
        
        $collection = DB::table("categories")->whereIn("id", ["FASHION", "FOOD", "LAPTOP"])->get();

        $collection->each(fn ($item) => info(json_encode($item)));
        $this->assertCount(3, $collection);
    }

    public function testWhereNull()
    {
        $this->insertCategories();
        
        $collection = DB::table("categories")->whereNull("description")->get();

        $collection->each(fn ($item) => info(json_encode($item)));
        $this->assertCount(4, $collection);
    }

    public function testWhereDate()
    {
        $this->insertCategories();
        
        $collection = DB::table("categories")->whereDate("created_at", "2020-10-10")->get();

        $collection->each(fn ($item) => info(json_encode($item)));
        $this->assertCount(4, $collection);
    }

    public function testUpdate()
    {
        $this->insertCategories();
        
        DB::table("categories")->where("id", "SMARTPHONE")->update([
            "name" => "Handphone"
        ]);

        $collection = DB::table("categories")->where("name", "Handphone")->get();

        $collection->each(fn ($item) => info(json_encode($item)));
        $this->assertCount(1, $collection);
    }

    public function testUpdateOrInsert()
    {
        DB::table("categories")->updateOrInsert(["id" => "SMARTPHONE"], [
            "id" => "SMARTPHONE",
            "name" => "Smartphone",
            "created_at" => "2020-10-10 10:10:10"
        ]);

        $collection = DB::table("categories")->where("name", "Smartphone")->get();

        $collection->each(fn ($item) => info(json_encode($item)));
        $this->assertCount(1, $collection);
    }

    public function testIncrement()
    {
        $this->seed(CounterSeeder::class);
        DB::table("counters")->where("id", "sample")->increment("counter", 1);

        $collection = DB::table("counters")->where("id", "sample")->get();
        $collection->each(fn ($item) => info(json_encode($item)));
        $this->assertCount(1, $collection);
    }

    public function testDelete()
    {
        $this->insertCategories();

        DB::table("categories")->where("id", "SMARTPHONE")->delete();
        $collection = DB::table("counters")->where("id", "SMARTPHONE")->get();
        $this->assertCount(0, $collection);
    }

    protected function insertProduct()
    {
        $this->insertCategories();
        
        DB::table("products")->insert([
            "id" => "1",
            "name" => "Iphone 14 Pro max",
            "category_id" => "SMARTPHONE",
            "price" => 20000000
        ]);

        DB::table("products")->insert([
            "id" => "2",
            "name" => "Samsung Galaxy",
            "category_id" => "SMARTPHONE",
            "price" => 18000000
        ]);
    }

    protected function insertProductFood()
    {
        DB::table("products")->insert([
            "id" => "3",
            "name" => "Gudeg",
            "category_id" => "FOOD",
            "price" => 200000
        ]);

        DB::table("products")->insert([
            "id" => "4",
            "name" => "Es Cendol",
            "category_id" => "FOOD",
            "price" => 10000
        ]);

        DB::table("products")->insert([
            "id" => "5",
            "name" => "cingur",
            "category_id" => "FOOD",
            "price" => 15000
        ]);
    }

    public function testJoin()
    {
        $this->insertProduct();

        $collection = DB::table("products")
                    ->join("categories", "products.category_id", "=", "categories.id")
                    ->select("products.id", "products.name", "products.price", "categories.name AS category_name")
                    ->get();

        $collection->each(fn ($item) => info(json_encode($item)));
        $this->assertCount(2, $collection);
    }

    public function testOrdering()
    {
        $this->insertProduct();

        $collection = DB::table("products")
                    ->orderBy("price")
                    ->orderBy("name")
                    ->get();
        $collection->each(fn ($item) => info(json_encode($item)));
        $this->assertCount(2, $collection);
    }

    public function testPaging()
    {
        $this->insertCategories();

        $collection = DB::table("categories")
                    ->skip(2)
                    ->take(2)
                    ->get();
        $collection->each(fn ($item) => info(json_encode($item)));
        $this->assertCount(2, $collection);
    }

    public function testChunk()
    {
        $this->insertManyCategories();

        DB::table("categories")
            ->orderBy("id")
            ->chunk(10, function ($categories) {
                info("start chunk");
                self::assertNotNull($categories);
                $categories->each( fn ($category) => info(json_encode($category)));
                info("end chunk");
            });
    }

    public function testLazy()
    {
        $this->insertManyCategories();

        $collection = DB::table("categories")->orderBy("id")->lazy(10);
        $this->assertNotNull($collection);
        $collection->each(fn ($item) => info(json_encode($item)));
    }

    public function testCursor()
    {
        $this->insertManyCategories();

        $collection = DB::table("categories")->orderBy("id")->cursor(10);
        $this->assertNotNull($collection);
        $collection->each(fn ($item) => info(json_encode($item)));
    }

    public function testAggregate()
    {
        $this->insertProduct();

        $result = DB::table("products")->count("id");
        $this->assertEquals(2, $result);
        
        $result = DB::table("products")->min("price");
        $this->assertEquals(18000000, $result);

        $result = DB::table("products")->max("price");
        $this->assertEquals(20000000, $result);

        $result = DB::table("products")->sum("price");
        $this->assertEquals(38000000, $result);
    }

    public function testQueryBuilderRaw()
    {
        $this->insertProduct();

        $collection = DB::table("products")
                            ->select(
                                DB::raw("count(id) AS total_product"),
                                DB::raw("min(price) AS min_price"),
                                DB::raw("max(price) AS max_price"),
                            )
                            ->get();
        $this->assertEquals(2, $collection[0]->total_product);
        $this->assertEquals(18000000, $collection[0]->min_price);
        $this->assertEquals(20000000, $collection[0]->max_price);
    }

    public function testGroupBy()
    {
        $this->insertProduct();
        $this->insertProductFood();

        $collection = DB::table("products")
                ->select("category_id", DB::raw("count(id) AS total_product"))
                ->groupBy("category_id")
                ->orderBy("category_id", "desc")
                ->get();
        info($collection->all());
        $this->assertCount(2, $collection);
        $this->assertEquals("SMARTPHONE", $collection[0]->category_id);
        $this->assertEquals("FOOD", $collection[1]->category_id);
    }

    public function testGroupByHaving()
    {
        $this->insertProduct();
        $this->insertProductFood();

        $collection = DB::table("products")
                ->select("category_id", DB::raw("count(id) AS total_product"))
                ->groupBy("category_id")
                ->having(DB::raw("count(id)"), ">", 2)
                ->orderBy("category_id", "desc")
                ->get();
        $this->assertCount(1, $collection);
    }

    public function testLocking()
    {
        $this->insertProduct();

        DB::transaction(function () {
            $collection = DB::table("products")
                        ->where("id", "=", "1")
                        ->lockForUpdate()
                        ->get();
            $this->assertCount(1, $collection);
        });
    }

    public function testPagination()
    {
        $this->insertCategories();

        $paginate = DB::table('categories')->paginate(perPage: 2, page:1);
        $this->assertEquals(1, $paginate->currentPage());
        $this->assertEquals(2, $paginate->perPage());
        $this->assertEquals(2, $paginate->lastPage());
        $this->assertEquals(4, $paginate->total());

        $collection = $paginate->items();
        $this->assertCount(2, $collection);
        foreach ($collection as $key => $value) {
            info(json_encode($value));
            
        }
    }

    public function testIterateAllPagination()
    {
        $this->insertCategories();

        $page = 1;

        while (true) {
            $paginate = DB::table('categories')->paginate(perPage: 2, page:$page);

            if($paginate->isEmpty()){
                break;
            }else{
                $page++;

                $collection = $paginate->items();
                $this->assertNotNull($collection);
                foreach ($collection as $key => $value) {
                    info(json_encode($value));
                    
                }
            }
        }
    }

    public function testCursorPagination()
    {
        $this->insertCategories();

        $cursor = "id";

        while (true) {
            $paginate = DB::table("categories")->orderBy("id")->cursorPaginate(perPage:2, cursor: $cursor);

            foreach ($paginate->items() as $item) {
                self::assertNotNull($item);
                info(json_encode($item));
            }
            $cursor = $paginate->nextCursor();

            if($cursor == null){
                break;
            }
        }
    }
}
