@extends("laravel-queue::layout")

@section('content')
    <h2>Total: {{$total ?? count($rows)}}</h2>
    <div class="py-4"></div>
    @foreach($rows as $row)
        @include("laravel-queue::list", $row)
        <div class="py-4"></div>
    @endforeach
@endsection
