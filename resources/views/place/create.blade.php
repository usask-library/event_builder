@extends('layouts.app')

@section('title', 'Add New Place')

@section('content')
    <div class="container-fluid">
        <h1>Add New Place</h1>

        <form method="post" action="{{ route('place.store') }}">
            @csrf
            @include('place.form')
            <button type="submit" class="btn btn-primary">Add place</button>
        </form>

    </div>
@endsection
