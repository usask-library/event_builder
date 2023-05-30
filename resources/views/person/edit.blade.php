@extends('layouts.app')

@section('title', 'Edit Person')

@section('content')
    <div class="container-fluid">
        <h1>Edit Person</h1>

        <form method="post" action="{{ route('person.update', $person->id) }}">
            @method('PUT')
            @csrf
            @include('person.form')
            <button type="submit" class="btn btn-primary">Update person</button>
        </form>
    </div>
@endsection
