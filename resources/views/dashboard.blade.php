@extends('layouts.app')

@section('content')
    @livewire('dashboard')
    @livewire('onu-chart')
    @livewire('olt-management')
@endsection
