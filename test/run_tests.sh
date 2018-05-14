#!/bin/sh

cd $(dirname $0)
exec ../vendor/bin/phpunit lazy_loader_test.php
