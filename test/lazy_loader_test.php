<?php
use PHPUnit\Framework\TestCase;

class LazyLoaderTest extends TestCase {
	
	function test(){
		$ll = new LazyLoader();
		
		$ll->set("job",function(){ return "Painter"; });
		$ll["greeting"] = function(){ return "Hello World!"; };
		$ll->setFavouriteDrink(function($time_period = ""){
			switch($time_period){
				case "morning":
					return "Tea";
				case "evening":
					return "Beer";
				default:
					return "Coffee";
			}
		});

		$this->assertEquals("Painter",$ll->get("job"));
		$this->assertEquals("Hello World!",$ll->get("greeting"));
		$this->assertEquals("Coffee",$ll->get("favourite_drink"));

		$this->assertEquals("Painter",$ll["job"]);
		$this->assertEquals("Hello World!",$ll["greeting"]);
		$this->assertEquals("Coffee",$ll["favourite_drink"]);

		$this->assertEquals("Painter",$ll->getJob());
		$this->assertEquals("Hello World!",$ll->getGreeting());
		$this->assertEquals("Coffee",$ll->getFavouriteDrink());

		$this->assertEquals("Tea",$ll->getFavouriteDrink("morning"));
		$this->assertEquals("Beer",$ll->getFavouriteDrink("evening"));

		// defined
		$this->assertEquals(true,$ll->defined("job"));
		$this->assertEquals(false,$ll->defined("unitialized"));

		// requesting uninitialized value throws an exception
		$exception_msg = "";
		try{
			$ll["unitialized"];
		}catch(Exception $e){
			$exception_msg = $e->getMessage();
		}
		$this->assertEquals("LazyLoader: uninitialized value requested: unitialized",$exception_msg);
	}
}
