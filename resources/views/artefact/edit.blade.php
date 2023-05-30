@extends('layouts.app')

@section('title', 'Edit Object')

@section('content')
    <div class="container-fluid">
        <h1>Edit Object</h1>

        <form method="post" action="{{ route('object.update', $object->id) }}">
            @method('PUT')
            @csrf
            @include('artefact.form')
            <button type="submit" class="btn btn-primary">Update object</button>
        </form>
    </div>
@endsection
