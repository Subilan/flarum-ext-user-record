<?php

require "vendor/autoload.php";
require "listeners.php";

use Flarum\Extend;

return [
    (new Extend\Event)->subscribe(PostEventListener::class)
];