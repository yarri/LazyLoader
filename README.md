LazyLoader
==========

LazyLoader provides simple mechanism for lazy loading with closures.

Basic Usage
-----------

LazyLoader implements ArrayAccess, thus the easiest way how to use is like an associative array.

    $lazy_loader = new LazyLoader();

    // Setting up closure
    $lazy_loader["recent_articles"] = function(){
       return Article::FindAll(["order_by" => "published_at DESC", "limit" => 10]);
    };

    // Reading - closure is being executed just in the first occurence of reading
    if($lazy_loader["recent_articles"]){
      foreach($lazy_loader["recent_articles"] as $article){
        // ...
      }
    }

Usage in a template engine
--------------------------

LazyLoader can be gracefully used in any template engine, for instance in [the Smarty](http://www.smarty.net/).

Preparing data for a Smarty template:

    $smarty->assign("lazy_loader",$lazy_loader);

In a Smarty template:

    {if $lazy_loader.recent_articles}

      <h3>Recent Articles</h3>

      <ul>
        {foreach $lazy_loader.recent_articles as $article}
          <li><a href="{$article->getUrl()}">{$article->getTitle()}</a></li>
        {/foreach}
      </ul>

    {/if}

Usage in the ATK14 Framework
----------------------------
  
    <?php
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

Recent articles are displayed on every page in the sidebar. So a caching is appropriate.

    {* file: app/layouts/default.tpl *}

    <div class="sidebar">
      {cache key=recent_articles expired=600}
        {render partial="shared/recent_articles" recent_articles=$lazy_loader.recent_articles}
      {/cache}
    </div>

A shared template doesn't have to know anything about lazy loading.

    {* file: app/views/shared/_recent_articles.tpl *}

    {if $recent_articles}

      <h3>Recent Articles</h3>

      <ul>
        {foreach $recent_articles as $article}
          <li><a href="{$article->getUrl()}">{$article->getTitle()}</a></li>
        {/foreach}
      </ul>

    {/if}

As you may expected, the "recent_articles" closure is executed only when the cache is re-created.

Lazy loading with arguments
---------------------------

    $lazy_loader->setRecentArticles(function($limit = 5){
       return Article::FindAll(["order_by" => "published_at DESC", "limit" => $limit]);
    });

    $ten_recent_articles = $lazy_loader->getRecentArticles(10);
 
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
