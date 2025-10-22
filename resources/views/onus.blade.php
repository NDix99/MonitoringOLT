@extends('layouts.app')

@section('content')
    @livewire('onu-dashboard')
@endsection

@section('scripts')
<script>
    // Add any custom JavaScript here if needed
    document.addEventListener('livewire:init', () => {
        Livewire.on('show-message', (message) => {
            alert(message);
        });
    });
</script>
@endsection
