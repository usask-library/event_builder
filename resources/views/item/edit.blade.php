@extends('layouts.app')

@section('title', 'Edit Item/Identifier')

@section('content')
    <div class="container-fluid">
        <h1>Edit Item</h1>

        <form method="post" action="{{ route('item.update', $item->id) }}">
            @method('PUT')
            @csrf
            @include('item.form')
            <button type="submit" class="btn btn-primary">Update item</button>
        </form>
    </div>
@endsection
