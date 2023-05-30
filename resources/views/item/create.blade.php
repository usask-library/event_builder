@extends('layouts.app')

@section('title', 'Add New Item/Identifier')

@section('content')
    <div class="container-fluid">
        <h1>Add New Item</h1>

        <form method="post" action="{{ route('item.store') }}">
            @csrf
            @include('item.form')
            <button type="submit" class="btn btn-primary">Add item</button>
        </form>

    </div>
@endsection
