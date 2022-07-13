@extends("laravel-queue::layout")

@section('content')
    <table class="table table-bordered table-striped text-nowrap table-hover">
        <thead class="">
        <tr>
            <th scope="col"><a href="?order=created_at&dir={{!request('dir')}}">#</a> {{request('order')==="created_at"?(!request('dir') ? "[a]" : "[d]"):""}}</th>
            <th scope="col"><a href="?order=name&dir={{!request('dir')}}">Name</a> {{request('order')==="name"?(!request('dir') ? "[a]" : "[d]"):""}}</th>
            <th scope="col"><a href="?order=job&dir={{!request('dir')}}">Job</a> {{request('order')==="job"?(!request('dir') ? "[a]" : "[d]"):""}}</th>
            <th scope="col"><a href="?order=model&dir={{!request('dir')}}">Model</a> {{request('order')==="model"?(!request('dir') ? "[a]" : "[d]"):""}}</th>
            <th scope="col"><a href="?order=model_id&dir={{!request('dir')}}">Model Id</a> {{request('order')==="model_id"?(!request('dir') ? "[a]" : "[d]"):""}}</th>
            <th scope="col"><a href="?order=arguments&dir={{!request('dir')}}">Arguments</a> {{request('order')==="arguments"?(!request('dir') ? "[a]" : "[d]"):""}}</th>
            <th scope="col"><a href="?order=date&dir={{!request('dir')}}">Valid At</a> {{request('order')==="date"?(!request('dir') ? "[a]" : "[d]"):""}}</th>
            <th scope="col"><a href="?order=result&dir={{!request('dir')}}">Result</a> {{request('order')==="result"?(!request('dir') ? "[a]" : "[d]"):""}}</th>
            <th scope="col"><a href="?order=result_at&dir={{!request('dir')}}">Result Date</a> {{request('order')==="result_at"?(!request('dir') ? "[a]" : "[d]"):""}}</th>
            <th scope="col"><a href="?order=created_at&dir={{!request('dir')}}">Created At</a> {{request('order')==="created_at"?(!request('dir') ? "[a]" : "[d]"):""}}</th>
        </tr>
        </thead>
        <tbody class="">
        @php
            $counter = 1;
        @endphp

        @foreach($rows as $row)
            <tr>
                <th scope="row">{{$counter++}}</th>
                <td>{{$row['name'] ?? '-'}}</td>
                <td>{{$row['job'] ?? '-'}}</td>
                <td>{{$row['model'] ?? '-'}}</td>
                <td>{{$row['model_id'] ?? '-'}}</td>
                <td>{{$row['arguments'] ?? '-'}}</td>
                <td>{{$row['date'] ? \Carbon\Carbon::parse($row['date']) : '-'}}</td>
                <td>{{$row['result'] ?? '-'}}</td>
                <td>{{$row['result_at'] ? \Carbon\Carbon::parse($row['result_at']) : '-'}}</td>
                <td>{{$row['created_at'] ? \Carbon\Carbon::parse($row['created_at']) : '-'}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
