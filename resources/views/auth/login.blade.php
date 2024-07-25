@extends('layouts.app_auth')

@section('content')
    <!-- wrapper -->
	<div class="wrapper">
		<div class="section-authentication-login d-flex align-items-center justify-content-center">
			<div class="row">
				<div class="col-12 col-lg-10 mx-auto">
					<div class="card radius-15">
						<div class="row no-gutters">
							<div class="col-lg-6">
								<div class="card-body p-md-5">
									<form method="POST" action="{{ route('login') }}">
                                        @csrf

                                        <div class="text-center">
                                            <img src="{{asset('images/logo.png')}}" width="80" alt="">
                                            <h3 class="mt-4 font-weight-bold">Bienvenido</h3>
                                        </div>

                                        <div class="login-separater text-center"> <span>INICIAR SESIÓN</span>
                                            <hr/>
                                        </div>

                                        <!-- Session Status -->
                                        <x-auth-session-status class="mb-4" :status="session('status')" />

                                        <!-- Validation Errors -->
                                        <x-auth-validation-errors class="mb-4" :errors="$errors" />

                                        <div class="form-group mt-4">
                                            <x-label for="email" :value="__('Email')" />
                                            <x-input id="email" class="form-control" type="email" name="email" :value="old('email')" 
                                            placeholder="Ingrese su dirección de correo electrónico" required autofocus />
                                        </div>

                                        <div class="form-group">
                                            <x-label for="password" :value="__('Password')" />
                                            <x-input id="password" class="form-control" type="password"
                                            name="password" placeholder="Ingresa tu contraseña" required autocomplete="current-password" />
                                        </div>

                                        <div class="form-row">
                                            <div class="form-group col">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="customSwitch1" name="remember" checked>
                                                    <label class="custom-control-label" for="customSwitch1">Recuérdame</label>
                                                </div>
                                            </div>
                                            
                                            @if (Route::has('password.request'))
                                                <div class="form-group col text-right"> 
                                                    <a href="authentication-forgot-password.html">
                                                        <i class='bx bxs-key mr-2'></i>¿Contraseña olvidada?
                                                    </a>
                                                </div>
                                            @endif
                                        </div>

                                        <input type="hidden" name="token_firebase" id="token_firebase">

                                        <div class="btn-group mt-3 w-100">
                                            <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
                                            <button type="button" class="btn btn-primary"><i class="lni lni-arrow-right"></i>
                                            </button>
                                        </div>    
                                    </form>									
								</div>
							</div>
							<div class="col-lg-6">
								<img src="{{asset('assets/images/login-images/login-frent-img.jpg')}}" class="card-img login-img h-100" alt="...">
							</div>
						</div>
						<!--end row-->
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end wrapper -->
@endsection

@section('asset_end')
    <!-- Firebase -->
    <script src="https://www.gstatic.com/firebasejs/8.3.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.3.0/firebase-messaging.js"></script>
    
    <script>
        $(document).ready(function(){
            var firebaseConfig = {
                apiKey: "AIzaSyCL0m8D9ZUR29mKiaYbZXjbbWJaQxeJtlE",
                authDomain: "pruebas-33641.firebaseapp.com",
                projectId: "pruebas-33641",
                storageBucket: "pruebas-33641.appspot.com",
                messagingSenderId: "688870553131",
                appId: "1:688870553131:web:e064e35fe33b06978fdc6e",
                measurementId: "G-YRHSYZXRYX"
            };
            // Initialize Firebase
            !firebase.apps.length ? firebase.initializeApp(firebaseConfig) : firebase.app();

            const messaging = firebase.messaging();

            configureNotification(messaging).then(function () {
                return messaging.getToken();
            })
            .then(token => {
                $("#token_firebase").val(token);
                return token;
            })
            .catch(function (err) {
                console.log(err);
                return err;                    
            })

            async function configureNotification (messaging) {
                await Notification.requestPermission()
            }
        })
    </script>
@endsection
