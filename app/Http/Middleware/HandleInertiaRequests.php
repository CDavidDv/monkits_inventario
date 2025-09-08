<?php

namespace App\Http\Middleware;

use App\Models\Hornos;
use App\Models\ControlProduccion;
use App\Models\Inventario;
use App\Models\Estimaciones;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        //si el usuario esta autenticado
        if ($request->user()) {
        } else {
            // si no esta autenticado
        }

        return array_merge(parent::share($request), [
            'user.roles' => $request->user() ? $request->user()->roles->pluck('name') : [],
            'user.permissions' => $request->user() ? $request->user()->getPermissionsViaRoles()->pluck('name') : [],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
                'message' => fn () => $request->session()->get('message')
            ],
            'auth' => [
                'user' => $request->user(),
            ],
        ]);
    }
}
