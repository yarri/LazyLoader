<?php
/**
 * Provides simple mechanism for lazy loading with closures
 *
 *	$lazy_loader = new LazyLoader();
 *
 *	// setting
 * 	$lazy_loader->set("recent_articles",function(){ return Article::FindAll(["order_by" => "published_at DESC", "limit" => 10]);} );
 *	// or
 * 	$lazy_loader["recent_articles"] = function(){ return Article::FindAll(["order_by" => "published_at DESC", "limit" => 10]);};
 *	// or
 * 	$lazy_loader->setRecentArticles(function(){ return Article::FindAll(["order_by" => "published_at DESC", "limit" => 10]);} );
 *
 *	// getting
 *	$lazy_loader->get("recent_articles");
 *	// or
 *	$lazy_loader["recent_articles"];
 *	// or
 *	$lazy_loader->getRecentArticles();
 *
 * Lazy loading with arguments is also possible.
 *
 * 	$lazy_loader->setRecentArticles($limit = 10){
 *		return Article::FindAll(["order_by" => "published_at DESC", "limit" => $limit]);
 *	}
 *
 *	$lazy_loader->getRecentArticles();
 *	$lazy_loader->getRecentArticles(5);
 *
 * The already read values are cached, thus the same calls are actually not repeated.
 */
class LazyLoader implements ArrayAccess {

	protected $closures = array();
	protected $cache = array();
	protected $stop_watch = null;

	function __construct(){
		$this->stop_watch = new StopWatch();
	}

	/**
	 *	$lazy_loader->set("recent_articles",function(){
	 *		return Article::FindAll(["order_by" => "published_at DESC", "limit" => 10]);
	 *	});
	 */
	function set($key,$closure){
		$this->closures[$key] = $closure;
	}

	/**
	 *	$recent_articles = $lazy_loader->get("recent_articles");
	 */
	function get($key,$options = array()){
		$options += array(
			"arguments" => array()
		);

		if(!isset($this->closures[$key])){
			throw new Exception("LazyLoader: uninitialized value requested: $key");
		}

		$arguments = $options["arguments"];
		$arguments_md5 = md5(serialize($arguments));

		if(!isset($this->cache[$key])){
			$this->cache[$key] = array();
		}

		if(!array_key_exists($arguments_md5,$this->cache[$key])){
			$closure = $this->closures[$key];
			$this->stop_watch->start($key);
			$this->cache[$key][$arguments_md5] = call_user_func_array($closure,$arguments);
			$this->stop_watch->stop($key);
		}

		return $this->cache[$key][$arguments_md5];
	}

	/**
	 *
	 *	$articles = [];
	 *	$lazy_loader->defined("recent_articles"){
	 *		$articles = $lazy_loader["recent_articles"];
	 *	}
	 */
	function defined($key){
		return array_key_exists($key,$this->closures);
	}

	function profilerData(){
		$out = array();
		foreach($this->closures as $key => $c){
			$out[$key] = array(
				"executed" => !isset($this->cache[$key]) ? 0 : sizeof($this->cache[$key]),
				"duration" => $this->stop_watch->getResult($key),
			);
		}
		return $out;
	}

	/**
	 * Magic method changes calling to an nonexistent method in this way:
	 *
	 * $lazy_loader->getRecentArticles() -> $lazy_loader->get("recent_articles");
	 * $lazy_loader->setRecentArticles(function(){ ... }) -> $lazy_loader->set("recent_articles",function(){ ... });
	 *
	 * @param string $name
	 * @param string $arguments
	 * @ignore
	 */
	function __call($name,$arguments){
		$name = new String4($name);
		if($name->match("/^(get|set)(.+)/",$matches)){
			$get_set = $matches[1]; // "get" or "set" 
			$key = $matches[2]->underscore(); // "RecentArticles" -> "recent_articles"
			if($get_set=="get"){
				return $this->get("$key",array("arguments" => $arguments));
			}
			return $this->set("$key",$arguments[0]);
		}

		throw new Exception("LazyLoader::__call(): unknown method $name()");
	}

	/*** functions implementing array like access ***/

	/**
	 * @ignore
	 */
	#[\ReturnTypeWillChange]
	function offsetExists($key){ return in_array($key,$this->closures); }

	/**
	 * @ignore
	 */
	#[\ReturnTypeWillChange]
	function offsetGet($key){ return $this->get($key); }

	/**
	 * @ignore
	 */
	#[\ReturnTypeWillChange]
	function offsetSet($key,$closure){ return $this->set($key,$closure); }

	/**
	 * @ignore
	 */
	#[\ReturnTypeWillChange]
	function offsetUnset($key){ unset($this->closures[$key]); unset($this->cache[$key]); }
}
