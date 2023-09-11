
            @extends('adminlte::page')

            @section('title', 'Dashboard')

            @section('content_header')
                <h1>Page not found</h1>
            @stop

            @section('content')
                <div class='error-page'>
                    <h2 class='headline text-warning'> 404</h2>
                    <div class='error-content'>
                        <h3><i class='fas fa-exclamation-triangle text-warning'></i> Oops! Page not found.</h3>
                        <p>
                            We could not find the page you were looking for.
                            Meanwhile, you may <a href='../../index.html'>return to dashboard</a> or try using the search form.
                        </p>
                    </div>
                </div>
            @stop

            @section('css')
                <link rel='stylesheet' href='/css/admin_custom.css'>
            @stop

            @section('js')
                <script>
                    console.log('Hi!');
                </script>
            @stop
        