<!--navigation-->
<div class="nav-container">
    <div class="mobile-topbar-header">
        <div class="">
            <img src="{{asset('images/logo.svg')}}" class="logo-icon-2" alt="" />
        </div>
        <div>
            <h4 class="logo-text">Tuappmin</h4>
        </div>
        <a href="javascript:;" class="toggle-btn ml-auto"> <i class="bx bx-menu"></i>
        </a>
    </div>
    <nav class="topbar-nav">
        <ul class="metismenu" id="menu">
            <li>
                <a href="{{ route('dashboard') }}">
                    <div class="parent-icon icon-color-1"><i class="bx bx-home-alt"></i>
                    </div>
                    <div class="menu-title">Dashboard</div>
                </a>
            </li>

            @if(Auth::user()->rol_id == 2 || Auth::user()->rol_id == 3)
                <li>
                    <a href="{{ route('configuracion.index') }}">
                        <div class="parent-icon icon-color-3"><i class="bx bx-cog"></i>
                        </div>
                        <div class="menu-title">Configuración</div>
                    </a>                
                </li>
            @endif

            @if(Auth::user()->rol_id == 2 || Auth::user()->rol_id == 3)
                <li>
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon icon-color-2"><i class="bx bx-list-ul"></i>
                        </div>
                        <div class="menu-title">Administración</div>
                    </a>
                    <ul>
                        @if($residencia->tipo == 'Apartamento')
                            <li> <a href="{{ route('apartamento.index') }}"><i class="bx bx-right-arrow-alt"></i>Apartamentos</a>
                            </li>
                        @else
                            <li> <a href="{{ route('casa.index') }}"><i class="bx bx-right-arrow-alt"></i>Casas</a>
                            </li>
                        @endif

                        <li> <a href="{{ route('zona_comun.index') }}"><i class="bx bx-right-arrow-alt"></i>Zonas comunes</a>
                        </li>

                        <li> <a href="{{ route('parking.index') }}"><i class="bx bx-right-arrow-alt"></i>Parqueadero</a>
                        </li>

                        <li> <a href="{{ route('public_service.index') }}"><i class="bx bx-right-arrow-alt"></i>Servicios públicos</a>
                        </li>

                        <li> <a href="{{ route('correspondence.index') }}"><i class="bx bx-right-arrow-alt"></i>Correspondencias</a>
                        </li>

                        <li> <a href="{{ route('visitante.index') }}"><i class="bx bx-right-arrow-alt"></i>Visitantes</a>
                        </li>

                        <li> <a href="{{ route('citofonia.index') }}"><i class="bx bx-right-arrow-alt"></i>Citofonia</a>
                        </li>

                        <li> <a href="{{ route('pqrs.index') }}"><i class="bx bx-right-arrow-alt"></i>PQRS</a>
                        </li>

                        <li> <a href="{{ route('comunicado.index') }}"><i class="bx bx-right-arrow-alt"></i>Comunicados</a>
                        </li>

                        <li> <a href="{{ route('pago.index') }}"><i class="bx bx-right-arrow-alt"></i>Pagos</a>
                        </li>

                        <li> <a href="{{ route('minuta.index') }}"><i class="bx bx-right-arrow-alt"></i>Minutas</a>
                        </li>
                    </ul>
                </li>
            @endif

            @if(Auth::user()->rol_id == 1 || Auth::user()->rol_id == 2 || Auth::user()->rol_id == 3)
                <li>
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon icon-color-2"><i class="bx bx-building"></i>
                        </div>
                        <div class="menu-title">Usuarios</div>
                    </a>
                    <ul>
                        <li> <a href="{{ route('usuario.index') }}"><i class="bx bx-right-arrow-alt"></i>{{ Auth::user()->rol_id == 1 ? 'Conjuntos residenciales' : 'Empleados' }}</a>
                        </li>
                        
                        <li> <a href="{{ route('residente.index') }}"><i class="bx bx-right-arrow-alt"></i>Residentes</a>
                        </li>
                    </ul>
                </li>
            @endif

            <li>
                <a href="{{ route('chat') }}">
                    <div class="parent-icon icon-color-5"><i class="bx bx-support"></i>
                    </div>
                    <div class="menu-title">Soporte</div>
                </a>                
            </li>

            @if(Auth::user()->rol_id == 1)
                <li>
                    <a class="has-arrow" href="{{ route('notificacion_user.index') }}">
                        <div class="parent-icon icon-color-4"><i class="bx bx-notification"></i>
                        </div>
                        <div class="menu-title">Notificaciones</div>
                    </a>                
                </li>
            @endif
        </ul>
    </nav>
</div>
<!--end navigation-->