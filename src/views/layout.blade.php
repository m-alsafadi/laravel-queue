<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Laravel Queue</title>

    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">

    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        .b-example-divider {
            height: 3rem;
            background-color: rgba(0, 0, 0, .1);
            border: solid rgba(0, 0, 0, .15);
            border-width: 1px 0;
            box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
        }

        .b-example-vr {
            flex-shrink: 0;
            width: 1.5rem;
            height: 100vh;
        }

        .bi {
            vertical-align: -.125em;
            fill: currentColor;
        }

        .nav-scroller {
            position: relative;
            z-index: 2;
            height: 2.75rem;
            overflow-y: hidden;
        }

        .nav-scroller .nav {
            display: flex;
            flex-wrap: nowrap;
            padding-bottom: 1rem;
            margin-top: -1px;
            overflow-x: auto;
            text-align: center;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }

        main > .container {
            padding: 60px 15px 0;
        }

        .tiny {
            font-size: x-small;
        }

        a svg {
            filter: invert(1);
        }

        a:hover svg {
            filter: invert(0);
        }

        .cursor-pointer {
            cursor: pointer !important;
        }

        .container {
            margin: 0 !important;
            min-width: 100% !important;
        }
    </style>
    @stack('css')
</head>

<body class="d-flex flex-column h-100">

<header>
    <!-- Fixed navbar -->
    <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{route('laravel-queue')}}">Laravel Queue</a>

            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav me-auto mb-2 mb-md-0">
                    <li class="nav-item">
                        <a class="nav-link {{currentRoute()->getName() === 'laravel-queue.all' ? 'active' : ''}}" aria-current="page" href="{{route('laravel-queue.all')}}">All</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{currentRoute()->getName() === 'laravel-queue.failed' ? 'active' : ''}}" aria-current="page" href="{{route('laravel-queue.failed')}}">Failed</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{currentRoute()->getName() === 'laravel-queue.success' ? 'active' : ''}}" aria-current="page" href="{{route('laravel-queue.success')}}">Success</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{currentRoute()->getName() === 'laravel-queue.history' ? 'active' : ''}}" aria-current="page" href="{{route('laravel-queue.history')}}">History</a>
                    </li>
                </ul>
                @php
                    $auto_refresh = $auto_refresh ?? false;
                @endphp
                <form class="d-flex" role="search" method="get">
                    <input class="form-control me-2" type="text" name="search" autocomplete="off" placeholder="Search" aria-label="Search" value="{{request('search')}}">
                    <button class="btn btn-outline-success me-2" type="submit">Search</button>
                    <a class="btn {{$auto_refresh ? "btn-danger m-auto" : "btn-warning btn-sm "}} rounded-circle me-2 auto-refresh-btn " href="{{route('laravel-queue.toggle_auto_refresh')}}">
                        @if($auto_refresh)
                            0
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                 width="24" height="32"
                                 viewBox="0 0 30 30"
                                 style="">
                                <path
                                    d="M 15 3 C 12.031398 3 9.3028202 4.0834384 7.2070312 5.875 A 1.0001 1.0001 0 1 0 8.5058594 7.3945312 C 10.25407 5.9000929 12.516602 5 15 5 C 20.19656 5 24.450989 8.9379267 24.951172 14 L 22 14 L 26 20 L 30 14 L 26.949219 14 C 26.437925 7.8516588 21.277839 3 15 3 z M 4 10 L 0 16 L 3.0507812 16 C 3.562075 22.148341 8.7221607 27 15 27 C 17.968602 27 20.69718 25.916562 22.792969 24.125 A 1.0001 1.0001 0 1 0 21.494141 22.605469 C 19.74593 24.099907 17.483398 25 15 25 C 9.80344 25 5.5490109 21.062074 5.0488281 16 L 8 16 L 4 10 z"></path>
                            </svg>
                        @endif
                    </a>
                    <a class="btn btn-outline-info me-2 btn-sm rounded-circle" href="{{url('/')}}">
                        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                             width="24" height="32"
                             viewBox="0 0 64 64"
                             style="">
                            <path
                                d="M 32 9.0019531 C 31.225957 9.0019531 30.451863 9.2247995 29.78125 9.671875 L 5.78125 25.671875 C 3.9538194 26.890604 3.4532611 29.390166 4.671875 31.21875 C 5.845859 32.97845 8.1947613 33.469534 10 32.417969 L 10 53 A 1.0001 1.0001 0 0 0 11 54 L 21 54 A 1.0001 1.0001 0 1 0 21 52 L 12 52 L 12 30.605469 A 1.0001 1.0001 0 0 0 10.445312 29.773438 L 9.109375 30.664062 C 8.1811589 31.283772 6.9564342 31.039446 6.3359375 30.109375 C 5.7165514 29.179959 5.9620556 27.955208 6.890625 27.335938 L 30.890625 11.335938 C 31.565399 10.886088 32.434601 10.886088 33.109375 11.335938 L 57.109375 27.335938 C 58.037944 27.955208 58.283449 29.179959 57.664062 30.109375 C 57.274555 30.693636 56.643757 31 55.996094 31 C 55.61407 31 55.234406 30.893474 54.890625 30.664062 L 53.554688 29.773438 A 1.0001 1.0001 0 0 0 52 30.605469 L 52 52 L 27 52 L 27 31 L 37 31 L 37 38.732422 C 36.576988 38.452531 36.125457 38.252859 35.654297 38.179688 C 34.786371 38.044899 33.91515 38.22673 33.214844 38.708984 C 32.514538 39.191238 32 40.035337 32 41 C 32 41.957056 32.519219 42.791667 33.216797 43.267578 C 33.914375 43.743489 34.779929 43.925905 35.646484 43.796875 C 36.119122 43.726499 36.574791 43.529252 37 43.25 L 37 49 A 1.0001 1.0001 0 1 0 39 49 L 39 43 C 39 42.247112 38.873416 41.587139 38.673828 40.998047 C 38.87393 40.410772 39 39.752159 39 39 L 39 30 A 1.0001 1.0001 0 0 0 38 29 L 26 29 A 1.0001 1.0001 0 0 0 25 30 L 25 53 A 1.0001 1.0001 0 0 0 26 54 L 53 54 A 1.0001 1.0001 0 0 0 54 53 L 54 32.394531 C 54.627636 32.761057 55.309379 33 55.996094 33 C 57.288431 33 58.561632 32.368489 59.328125 31.21875 C 60.546739 29.390166 60.046181 26.890604 58.21875 25.671875 L 34.21875 9.671875 C 33.548137 9.2247995 32.774043 9.0019531 32 9.0019531 z M 35.0625 40.138672 C 35.155446 40.135537 35.250185 40.141416 35.345703 40.15625 C 35.761884 40.22088 36.181269 40.491448 36.507812 41.005859 C 36.184018 41.504294 35.767551 41.756709 35.353516 41.818359 C 34.970071 41.875449 34.585625 41.778919 34.345703 41.615234 C 34.105781 41.451552 34 41.287444 34 41 C 34 40.688163 34.110462 40.518809 34.347656 40.355469 C 34.525552 40.232964 34.783662 40.148075 35.0625 40.138672 z"></path>
                        </svg>
                    </a>
                </form>
            </div>
        </div>
    </nav>
</header>

<!-- Begin page content -->
<main class="flex-shrink-0">
    <div class="container">
        @yield('content')
    </div>
</main>

<!-- JavaScript Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous"></script>
    <script>
        (function () {
        @if($auto_refresh)
            let $autorefresh = document.body.querySelector(".auto-refresh-btn"),
                $counter = 0;

        $autorefresh && setInterval(() => {
                $autorefresh && ($autorefresh.innerText = ++$counter)
            }, 1000)
            setTimeout(() => document.location.reload(), 5000)
        @endif

        if( document.body.querySelector(".has-data-text") )
        {
            document.body.addEventListener("click", function (e) {
                if (!e || !e.target) {
                    return false;
                }
                let elm;
                elm = e.target.matches(".has-data-text") ? e.target : e.target.closest(".has-data-text")
                if (!elm) {
                    return false;
                }
                e.preventDefault()

                if (elm.innerText !== "...") {
                    elm.innerHTML = "..."
                } else {
                    elm.innerHTML = elm.getAttribute("data-text")
                }
            })
        }

        let elmsShowText = document.body.querySelectorAll('[data-show-text]');
        if( elmsShowText.length )
        {
            elmsShowText.forEach((e)=> {
                let should_show = e.getAttribute('data-show-text')
                if( should_show && !['0', 'null', 'undefined', 'false', ''].includes(should_show.toLowerCase()) )
                {
                    e.click && e.click() || e.dispatchEvent(new MouseEvent('click'))
                }
            })
        }
        })()
    </script>

@stack('js')
</body>
</html>
