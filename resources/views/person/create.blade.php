@extends('layouts.app')

@section('title', 'Add New Person')

@section('content')
    <div class="container-fluid">
        <h1>Add New Person</h1>

        <form method="post" action="{{ route('person.store') }}">
            @csrf
            @include('person.form')
            <button type="submit" class="btn btn-primary">Add person</button>
        </form>

    </div>
@endsection
