@extends('layouts.app')

@section('title', 'Edit Place')

@section('content')
    <div class="container-fluid">
        <h1>Edit Place</h1>

        <form method="post" action="{{ route('place.update', $place->id) }}">
            @method('PUT')
            @csrf
            @include('place.form')
            <button type="submit" class="btn btn-primary">Update place</button>
        </form>
    </div>
@endsection
