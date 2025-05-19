<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ ucfirst($title ?? '') }}</title>
        <!-- Scripts -->
        <script src="//code.jquery.com/jquery-3.3.1.js"></script>
        <script src="{{ asset('js/jQuery.print.min.js') }}" defer></script>
        <script src="{{ asset('js/app.js') }}" defer></script>
        <!-- Fonts -->
        <link rel="dns-prefetch" href="//fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCz4nbUEPbcIvrFHm5D55Llv5cfQFLa-fI&libraries=places&language=en"></script>

        <!-- Styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
        @yield('styles')
        <link rel="stylesheet" type="text/css" href="{{ asset('css/custom.css') }}"/>
    </head>
    <body>
        <div id="appProject">
            <nav class="navbar navbar-expand-md navbar-light navbar-laravel">
                <div class="container">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <!-- Left Side Of Navbar -->
                        <ul class="navbar-nav mr-auto">
                        </ul>
                        <!-- Right Side Of Navbar -->
                        <ul class="navbar-nav ml-auto">
                            <!-- Authentication Links -->
                            @guest
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @else
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                        <i class="fa fa-user-circle"></i> {{ ucfirst(Auth::user()->name) }} <span class="caret"></span>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                        <a class="dropdown-item" href="{{ url('builder') }}"> <i class="fa fa-tachometer"></i> Dashboard</a>
                                        <a class="dropdown-item" href="{{ route('list.user') }}"> <i class="fa fa-users"></i> Manage Users</a>
                                        <a href="{{route('edit.user', Auth::user()->id)}}" class="dropdown-item"><i class="fa fa-user"></i> My profile</a>
                                        <a href="{{route('shipping.index')}}" class="dropdown-item"><i class="fa fa-calculator"></i> Shipping Rates</a>
                                        <a href="{{route('settings.index')}}" class="dropdown-item"><i class="fa fa-gear"></i> Settings</a>
                                        <a class="dropdown-item" href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                            document.getElementById('logout-form').submit();">
                                            <i class="fa fa-sign-out"></i> {{ __('Logout') }}
                                        </a>
                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            @csrf
                                        </form>
                                    </div>
                                </li>
                            @endguest
                        </ul>
                        @guest
                        @else
                            <ul class="navbar-nav site-nav ml-auto hidden-lg hidden-md hidden-sm">
                                <li class="nav-item"><a href="{{route('builder.index')}}">Jersey Builders</a></li>
                                <li class="nav-item"><a href="{{route('builder.create')}}">Add New Builder </a></li>
                                <li class="nav-item"><a href="{{route('color.index')}}">Colours</a></li>
                                <li class="nav-item"><a href="{{route('sizes.index')}}">Sizes</a></li>
                                <li class="nav-item"><a href="{{route('quotes.index')}}">Inquiries</a></li>
                                <li class="nav-item"><a href="{{route('roster.index')}}">Rosters</a></li>
                                <li class="nav-item"><a href="{{route('design.index')}}">Designs</a></li>
                                <li class="nav-item"><a href="{{route('logger.index')}}">Logs</a></li>
                            </ul>
                        @endguest
                    </div>
                </div>
            </nav>
            <main class="py-4">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light2 top-nav hidden-xs">
                                    <div class="row">
                                        <div class="col-md-2 text-center"><a class="text-dark" href="{{route('builder.index')}}">Jersey Builders</a></div>
                                        <div class="col-md-2 text-center"><a class="text-dark" href="{{route('builder.create')}}">Add New Builder </a></div>
                                        <div class="col-md-2 text-center"><a class="text-dark" href="{{route('color.index')}}">Colours</a></div>
                                        <div class="col-md-1 text-center"><a class="text-dark" href="{{route('sizes.index')}}">Sizes</a></div>
                                        <div class="col-md-2 text-center"><a class="text-dark" href="{{route('quotes.index')}}">Inquiries</a></div>
                                        <div class="col-md-2 text-center"><a class="text-dark" href="{{route('roster.index')}}">Rosters</a></div>
                                        <div class="col-md-2 text-center"><a class="text-dark" href="{{route('design.index')}}">Designs</a></div>
                                        <div class="col-md-1 text-center"><a class="text-dark" href="{{route('logger.index')}}">Logs</a></div>
                                    </div>
                                </div>

                                @yield('content')
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </body>
    @yield('scripts')
    @if (session('status'))
    <script type="text/javascript">
    $(document).ready (function(){
    $(".alert-success").fadeTo(2000, 500).slideUp(500, function(){
    $(this).slideUp(500);
    });

    });
    </script>
    @endif
</html>
