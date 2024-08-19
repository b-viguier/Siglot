<?php

if (\PHP_OS_FAMILY === 'Windows') {
    require 'c:\tools\php\phpunit';
} else {
    require '/usr/local/bin/phpunit';
}
