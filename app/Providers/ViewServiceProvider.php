<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;

/** Models */
use App\Models\User;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer('*', function ($view)
        {
            $residencia = null;
            if(Auth::check()){
                /** Obtenemos la casa o el apartamento principal */
                $owner_id = null;
                if(Auth::user()->rol_id == 2){
                    $owner_id = Auth::id();
                }
                else{
                    $owner_id = Auth::user()->owner_id;
                }

                $residencia = User::find($owner_id);
            }

            $view->with('residencia', $residencia);
        });
    }
}
