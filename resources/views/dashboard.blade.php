@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4>Chat Dashboard</h4>
                </div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h5>Available Users</h5>
                    
                    @if(count($users) > 0)
                        <div class="list-group">
                            @foreach($users as $user)
                                <a href="{{ route('chat', $user) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $user->name }}</h6>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">Chat</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">No users available to chat with.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection