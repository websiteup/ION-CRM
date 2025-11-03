@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <p>{{ __('You are logged in!') }}</p>
                    
                    <div class="mt-4">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary me-2">Dashboard Admin</a>
                        <a href="{{ route('admin.clients.index') }}" class="btn btn-info me-2">Clienți</a>
                        <a href="{{ route('admin.services.index') }}" class="btn btn-success me-2">Servicii</a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-warning">Utilizatori</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
