<!DOCTYPE html>
<html lang="es">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>Tuappmin - Gestión de conjuntos residenciales</title>
	<!--favicon-->
	<link rel="icon" href="{{asset('assets/images/favicon-32x32.png')}}" type="image/png" />
	<!-- Vector CSS -->
	<link href="{{asset('assets/plugins/vectormap/jquery-jvectormap-2.0.2.css')}}" rel="stylesheet" />
	<!--plugins-->
	<link href="{{asset('assets/plugins/simplebar/css/simplebar.css')}}" rel="stylesheet" />
	<link href="{{asset('assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css')}}" rel="stylesheet" />
	<link href="{{asset('assets/plugins/metismenu/css/metisMenu.min.css')}}" rel="stylesheet" />
	<!-- loader-->
	<!--<link href="{{asset('assets/css/pace.min.css')}}" rel="stylesheet" />
	<script src="{{asset('assets/js/pace.min.js')}}"></script>-->
	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}" />
	<!-- Icons CSS -->
	<link rel="stylesheet" href="{{asset('assets/css/icons.css')}}" />
	<!-- App CSS -->
	<link rel="stylesheet" href="{{asset('assets/css/app.css')}}" />
	<link rel="stylesheet" href="{{asset('assets/css/dark-header.css')}}" />
	<link rel="stylesheet" href="{{asset('assets/css/dark-theme.css')}}" />
	<link rel="stylesheet" href="{{asset('assets/plugins/notifications/css/lobibox.min.css')}}" />
	<link rel="stylesheet" href="{{asset('assets/plugins/sweetalert2/sweetalert2.min.css')}}">
	<link href="{{asset('assets/plugins/dropify/css/dropify.css')}}" rel="stylesheet">

    <script src="{{ asset('js/app2.9.js') }}" defer></script>

    @yield('asset_init')
</head>

<body>
	<!-- wrapper -->
	<div class="wrapper" id="app">
		@if(Auth::check())
			@include('partials.header')
			@include('partials.navigation')
		@endif
		<!--page-wrapper-->
		<div class="page-wrapper">
			<!--page-content-wrapper-->
			<div class="page-content-wrapper">
				<div class="page-content">
					@include('partials.status')
					@yield('content')
				</div>
			</div>
			<!--end page-content-wrapper-->
		</div>
		<!--end page-wrapper-->
		<!--start overlay-->
		<div class="overlay toggle-btn-mobile"></div>
		<!--end overlay-->
		<!--Start Back To Top Button--> <a href="javascript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
		<!--End Back To Top Button-->
		@if(Auth::check())
			@include('partials.footer')
		@endif
	</div>

	<!-- Firebase -->
	<script src="https://www.gstatic.com/firebasejs/8.3.0/firebase-app.js"></script>
	<script src="https://www.gstatic.com/firebasejs/8.3.0/firebase-messaging.js"></script>

	<script>
		window.laravelEchoPort = '{{ env("LARAVEL_ECHO_PORT") }}';
	</script>
	
	<script src="//{{ request()->getHost() }}:{{ env("LARAVEL_ECHO_PORT") }}/socket.io/socket.io.js"></script>
	
	<!-- JavaScript -->
	<!-- jQuery first, then Popper.js, then Bootstrap JS -->
	<script src="{{asset('assets/js/jquery.min.js')}}"></script>
	<script src="{{asset('assets/js/popper.min.js')}}"></script>
	<script src="{{asset('assets/js/bootstrap.min.js')}}"></script>
	<!--plugins-->
	<script src="{{asset('assets/plugins/simplebar/js/simplebar.min.js')}}"></script>
	<script src="{{asset('assets/plugins/metismenu/js/metisMenu.min.js')}}"></script>
	<script src="{{asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js')}}"></script>
	<!-- Vector map JavaScript -->
	<script src="{{asset('assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js')}}"></script>
	<script src="{{asset('assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js')}}"></script>
	<script src="{{asset('assets/plugins/vectormap/jquery-jvectormap-in-mill.js')}}"></script>
	<script src="{{asset('assets/plugins/vectormap/jquery-jvectormap-us-aea-en.js')}}"></script>
	<script src="{{asset('assets/plugins/vectormap/jquery-jvectormap-uk-mill-en.js')}}"></script>
	<script src="{{asset('assets/plugins/vectormap/jquery-jvectormap-au-mill.js')}}"></script>	
	<script src="{{asset('assets/plugins/notifications/js/lobibox.min.js')}}"></script>
	<script src="{{asset('assets/plugins/notifications/js/notifications.min.js')}}"></script>
	<script src="{{asset('assets/plugins/sweetalert2/sweetalert2.all.min.js')}}"></script>
    <script src="{{asset('assets/plugins/sweetalert2/sweetalerts.js')}}"></script>
	<script src="{{asset('assets/plugins/dropify/js/dropify.min.js')}}"></script>
	
	<script>
		$(document).ready(function(){
			@if(Auth::check() && Auth::user()->rol_id != 1 && $residencia->status == 'Desactivado')
				$("#modal-status").modal('toggle');
			@endif

			@if(session('success'))
				Lobibox.notify('success', {
					pauseDelayOnHover: true,
					continueDelayOnInactiveTab: false,
					position: 'top right',
					icon: 'bx bx-check-circle',
					title: 'Bien hecho',
					msg: "{{ session('success') }}",
					sound: false,
					delay: 5000
				});
			@endif
		})
	</script>

    @yield('asset_end')

	<!-- App JS -->
	<script src="{{asset('assets/js/app.js')}}"></script>
</body>

</html>
