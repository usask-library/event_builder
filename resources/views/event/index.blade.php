@extends('layouts.app')

@section('title', 'Events')

@section('content')
    <div class="container-fluid">
        <h1>Events</h1>

        @include('event.list', ['showClass' => true])
    </div>
@endsection
