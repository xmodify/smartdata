@extends('layouts.admin')

@section('title', 'Admin Dashboard - SmartData')

@section('content')
<div class="mt-2">
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-body p-5">
                    <h2 class="text-success fw-bold">Welcome to Admin Dashboard</h2>
                    <p class="text-muted">You are logged in as an <strong>Administrator</strong>.</p>
                    <hr>
                    <div class="alert alert-info">
                        This is the template for the <strong>Admin</strong> role.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

