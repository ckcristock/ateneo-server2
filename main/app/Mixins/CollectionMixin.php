<?php

namespace App\Mixins;

use Illuminate\Support\Str;

class CollectionMixin
{
    /**
     * @return \Closure
     */

    // Collection::macro('toUpper', function () {
    //     return $this->map(function ($value) {
    //         return Str::upper($value);
    //     });
    // });

    public function toUpper()
    {
        return function () {
            return $this->map(function ($value) {
                return Str::upper($value);
            });
        };
    }
}
