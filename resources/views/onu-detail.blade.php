@extends('layouts.app')

@section('content')
    @livewire('onu-detail', ['onuId' => $onuId])
@endsection
