@extends('layouts.app')

@section('title', 'Add New Object')

@section('content')
    <div class="container-fluid">
        <h1>Add New Object</h1>

        <form method="post" action="{{ route('object.store') }}">
            @csrf
            @include('artefact.form')
            <button type="submit" class="btn btn-primary">Add object</button>
        </form>

    </div>
@endsection
