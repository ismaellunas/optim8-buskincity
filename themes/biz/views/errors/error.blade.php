@extends('errors.layout')

@section('title', $message)

@section('content')
    <h1>{{ $statusCode }}</h1>
    <p class="message">{{ __('Opss! Something Error') }}</p>
    <p class="sub-message">{{ $message }}</p>
@endsection