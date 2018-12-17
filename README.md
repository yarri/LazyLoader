LazyLoader
==========

[![Build Status](https://travis-ci.org/yarri/LazyLoader.svg?branch=master)](https://travis-ci.org/yarri/LazyLoader)

LazyLoader provides efficient mechanism for lazy loading with closures.

In LazyLoader one or more named closures can be defined. Any  closure is called at most once when its output is needed.

Basic Usage
-----------

LazyLoader implements ArrayAccess, thus the easiest way how to use is like an associative array.

    $lazy_loader = new LazyLoader();

    // Setting up a closure,
    $lazy_loader["recent_articles"] = function(){
       return Article::FindAll(["order_by" => "published_at DESC", "limit" => 10]);
    };
    // ... another closure
    $lazy_loader["top_product"] = function(){
      return Product::FindFirst(["order_by" => "pieces_sold DESC"]);
    }

    // Reading - closure is being executed only during the first occurence of reading
    if($lazy_loader["recent_articles"]){
      foreach($lazy_loader["recent_articles"] as $article){
        // ...
      }
    }
    //
    $top_product = $lazy_loader["top_product"];

There are total three ways how to set a closure or get its output. All of them do the same thing and can be mixed.

    // Setting a closure
    $lazy_loader->set("recent_articles",function(){ /* ... */ });
    // or
    $lazy_loader["recent_articles"] = function(){ /* ... */ };
    // or
    $lazy_loader->setRecentArticles(function(){ /* ... */ });

    // Getting the output
    $recent_articles = $lazy_loader->get("recent_articles");
    // or
    $recent_articles = $lazy_loader["recent_articles"];
    // or
    $recent_articles = $lazy_loader->getRecentArticles();

Also arguments can be involved. In this case the usage of camelized virtual methods comes in handy.

    $lazy_loader->setRecentArticles(function($limit = 10){
       return Article::FindAll(["order_by" => "published_at DESC", "limit" => $limit]);
    });

    $five_recent_articles = $lazy_loader->getRecentArticles(5);

### Usage in a template engine

LazyLoader can be gracefully used in any template engine, for instance in [the Smarty](http://www.smarty.net/).

Preparing data for a Smarty template:

    $smarty->assign("lazy_loader",$lazy_loader);

In a Smarty template:

    <h3>Recent Articles</h3>

    <ul>
      {foreach $lazy_loader.recent_articles as $article}
        <li><a href="{$article->getUrl()}">{$article->getTitle()}</a></li>
      {/foreach}
    </ul>

### Usage in the ATK14 Framework
  
    <?php
    // file: app/controllers/application.php

    class ApplicationController extends Atk14Controller {

      // ..

      function _before_render(){
        $lazy_loader = new $lazy_loader;

        $lazy_loader["recent_articles"] = function(){
          return Article::FindAll(["order_by" => "published_at DESC", "limit" => 10]);
        };

        $this->tpl_data["lazy_loader"] = $lazy_loader;
      }
    }

Recent articles are displayed on every page in the sidebar. So caching is appropriate.

    {* file: app/layouts/default.tpl *}

    {cache key=sidebar expire=600}
      <div class="sidebar">
          {render partial="shared/recent_articles" recent_articles=$lazy_loader.recent_articles}
      </div>
    {/cache}

A shared template doesn't have to know anything about lazy loading.

    {* file: app/views/shared/_recent_articles.tpl *}

    <h3>Recent Articles</h3>

    <ul>
      {foreach $recent_articles as $article}
        <li><a href="{$article->getUrl()}">{$article->getTitle()}</a></li>
      {/foreach}
    </ul>

As you may expected, the "recent_articles" closure is executed only when the cache is re-created.

### Tracy panel integration

LazyLoader package comes with LazyLoaderPanel for easy integration into the popular debugger Tracy (https://packagist.org/packages/tracy/tracy)

    $tracy_bar = Tracy\Debugger::getBar();
    $tracy_bar->addPanel(new LazyLoaderPanel($lazy_loader));

Installation
------------

The best way how to install LazyLoader is to use a Composer:

    composer require yarri/lazy-loader

Testing
-------

Install required dependencies for development:

    composer update --dev

Run tests:

    ./test/run_tests.sh

License
-------

LazyLoader is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license)

[//]: # ( vim: set ts=2 et: )
