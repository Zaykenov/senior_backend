@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4>Users Dashboard</h4>
                </div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h5>Users List</h5>
                    
                    @if(isset($users['data']) && count($users['data']) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Roles</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users['data'] as $user)
                                        <tr>
                                            <td>{{ $user['id'] }}</td>
                                            <td>{{ $user['name'] }}</td>
                                            <td>{{ $user['email'] }}</td>
                                            <td>
                                                @if(isset($user['roles']))
                                                    @foreach($user['roles'] as $role)
                                                        <span class="badge bg-primary">{{ $role['name'] }}</span>
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($user['created_at'])->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        @if(isset($users['links']) && isset($users['meta']))
                            <nav>
                                <ul class="pagination">
                                    @if($users['meta']['current_page'] > 1)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ url('/dashboard?page=1') }}">First</a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="{{ url('/dashboard?page=' . ($users['meta']['current_page'] - 1)) }}">Previous</a>
                                        </li>
                                    @endif
                                    
                                    @for($i = max(1, $users['meta']['current_page'] - 2); $i <= min($users['meta']['current_page'] + 2, $users['meta']['last_page']); $i++)
                                        <li class="page-item {{ $i == $users['meta']['current_page'] ? 'active' : '' }}">
                                            <a class="page-link" href="{{ url('/dashboard?page=' . $i) }}">{{ $i }}</a>
                                        </li>
                                    @endfor
                                    
                                    @if($users['meta']['current_page'] < $users['meta']['last_page'])
                                        <li class="page-item">
                                            <a class="page-link" href="{{ url('/dashboard?page=' . ($users['meta']['current_page'] + 1)) }}">Next</a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="{{ url('/dashboard?page=' . $users['meta']['last_page']) }}">Last</a>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        @endif
                    @else
                        <div class="alert alert-info">No users found or unable to fetch user data.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection