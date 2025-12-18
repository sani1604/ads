{{-- resources/views/client/profile/edit.blade.php --}}
@extends('layouts.client')

@section('title', 'Profile Settings')
@section('page-title', 'Profile Settings')

@section('content')
    <div class="row g-4">
        {{-- Profile Info --}}
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Company & Contact Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('client.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <x-form.input 
                                    name="name"
                                    label="Full Name"
                                    :required="true"
                                    :value="$user->name"
                                />
                            </div>
                            <div class="col-md-6">
                                <x-form.input 
                                    name="phone"
                                    label="Phone"
                                    :required="true"
                                    :value="$user->phone"
                                />
                            </div>
                            <div class="col-md-6">
                                <x-form.input 
                                    name="company_name"
                                    label="Company Name"
                                    :value="$user->company_name"
                                />
                            </div>
                            <div class="col-md-6">
                                <x-form.input 
                                    name="company_website"
                                    label="Website"
                                    type="url"
                                    :value="$user->company_website"
                                />
                            </div>
                            <div class="col-md-6">
                                <x-form.select 
                                    name="industry_id"
                                    label="Industry"
                                    :options="$industries->pluck('name', 'id')->toArray()"
                                    :selected="$user->industry_id"
                                    placeholder="Select industry"
                                />
                            </div>
                            <div class="col-md-6">
                                <x-form.input 
                                    name="gst_number"
                                    label="GST Number"
                                    :value="$user->gst_number"
                                />
                            </div>
                            <div class="col-12">
                                <x-form.textarea 
                                    name="address"
                                    label="Address"
                                    :value="$user->address"
                                    rows="2"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-form.input 
                                    name="city"
                                    label="City"
                                    :value="$user->city"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-form.input 
                                    name="state"
                                    label="State"
                                    :value="$user->state"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-form.input 
                                    name="postal_code"
                                    label="PIN Code"
                                    :value="$user->postal_code"
                                />
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Change Password --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('client.profile.change-password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <x-form.input 
                            name="current_password"
                            label="Current Password"
                            type="password"
                            :required="true"
                        />

                        <x-form.input 
                            name="password"
                            label="New Password"
                            type="password"
                            :required="true"
                            help="Minimum 8 characters, with uppercase, lowercase, and numbers."
                        />

                        <x-form.input 
                            name="password_confirmation"
                            label="Confirm New Password"
                            type="password"
                            :required="true"
                        />

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key me-1"></i>Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Avatar & Activity --}}
        <div class="col-lg-4">
            {{-- Avatar --}}
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="{{ $user->avatar_url }}" class="rounded-circle mb-3" width="96" height="96" alt="">
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted mb-2">{{ $user->company_name ?? 'Client' }}</p>

                    <form action="{{ route('client.profile.update-avatar') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <x-form.file 
                            name="avatar"
                            label="Change Profile Picture"
                            accept=".jpg,.jpeg,.png"
                            :preview="true"
                        />
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="fas fa-upload me-1"></i>Upload
                        </button>
                    </form>
                </div>
            </div>

            {{-- Quick Info --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Account Info</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Email</td>
                            <td class="text-end">{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Joined</td>
                            <td class="text-end">{{ $user->created_at->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Last Login</td>
                            <td class="text-end">
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'N/A' }}
                            </td>
                        </tr>
                    </table>
                    <div class="mt-3 text-end">
                        <a href="{{ route('client.profile.activity') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-history me-1"></i>View Activity
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection