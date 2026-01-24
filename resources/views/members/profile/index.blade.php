@extends('layouts.member')
@section('content')
<header class="header-2">
    <div class="page-header min-vh-35 relative" style="background-image: url('{{ asset('assets/img/bg9.jpg') }}')">
        <span class="mask bg-gradient-dark opacity-4"></span>
        <div class="container">
            <div class="row">
                <div class="col-12 mx-auto">
                    <h3 class="text-white pt-3 mt-n2">Profile</h3>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="card card-body blur shadow-blur mx-3 mx-md-4 mt-n6">

    <section class="pt-3 pb-4" id="count-stats">
        <div class="container">
            @if ($errors->any())
                <div class="row">
                    @foreach ($errors->all() as $error)
                        <div class="col-12 col-lg-6">
                            <div class="alert alert-danger text-white text-xs alert-dismissible fade show" role="alert">
                                {{ $error }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <form role="form" action="{{ route('profile_member.update', ['Id_Member' => $member->id]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="card-header bg-primary border-radius-xl">
                                <h5 class="card-title text-white">Profile</h5>
                            </div>
                            <div class="card-body">
                                <div class="input-group input-group-outline my-3 {{ $member->nama ? 'is-filled' : '' }}">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control @error('Name_Member') is-invalid @enderror" name="Name_Member" value="{{ $member->nama }}">
                                </div>
                                @error('Name_Member')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="card-footer">
{{--                                <button type="submit" class="btn bg-gradient-primary w-100 my-2">Update</button>--}}
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-6"></div>
            </div>
        </div>
    </section>
</div>
@endsection
