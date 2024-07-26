<?php

namespace App\Providers;

use App\Builder\RelationsBuilder;
use App\Mixins\CollectionMixin;
use App\Mixins\ResponseMixin;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Doctrine\DBAL\Types\Type;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Response::mixin(new ResponseMixin);
        Collection::mixin(new CollectionMixin);
        //Type::addType('enum', 'Doctrine\DBAL\Types\StringType');
        //Type::addType('double', 'Doctrine\DBAL\Types\FloatType');
        //Type::addType('tinyinteger', 'Doctrine\DBAL\Types\IntegerType');
        Blade::directive('money', function ($amount) {
            return "<?php echo '$' . number_format($amount, 2, ',', '.'); ?>";
        });
        Validator::extend('base64_file', function ($attribute, $value, $parameters, $validator) {
            if (preg_match('/^data:image\/(\w+);base64,/', $value)) {
                $file_type = explode(';', explode(':', substr($value, 0, strpos($value, ',')))[1])[0];
                return in_array($file_type, ['image/jpeg', 'image/png', 'image/jpg']);
            }
            return false;
        });
    }
}
