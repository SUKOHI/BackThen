<?php namespace Sukohi\BackThen\Facades;

use Illuminate\Support\Facades\Facade;

class BackThen extends Facade {

    /**
    * Get the registered name of the component.
    *
    * @return string
    */
    protected static function getFacadeAccessor() { return 'back-then'; }

}