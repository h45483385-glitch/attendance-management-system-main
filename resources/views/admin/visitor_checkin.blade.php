@extends('layouts.master')

@section('content')

<style>
    .visitor-card { border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: none; }
    .card-header-indigo { background: linear-gradient(135deg, #4f46e5, #3730a3); color: white; border-radius: 12px 12px 0 0 !important; padding: 20px 25px; }
    .form-control { border-radius: 8px; border: 1px solid #cbd5e1; padding: 12px 15px; font-size: 14px; color: #334155; }
    .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
    .form-group label { font-weight: 600; color: #475569; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
    .btn-indigo { background: linear-gradient(135deg, #4f46e5, #4338ca); color: white; border: none; font-weight: 600; border-radius: 8px; padding: 12px 30px; letter-spacing: 0.5px; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3); transition: all 0.3s; }
    .btn-indigo:hover { transform: translateY(-2px); color: white; box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4); }
</style>

<div class="container-fluid pt-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; border-radius: 8px;">
            <i class="mdi mdi-check-circle mr-2"></i><strong>Success!</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true" style="color: #065f46;">&times;</span>
            </button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="card visitor-card mt-3">
                <div class="card-header card-header-indigo d-flex align-items-center">
                    <i class="mdi mdi-account-badge-outline display-4 mr-3" style="font-size: 32px; opacity: 0.9;"></i>
                    <div>
                        <h3 class="card-title mb-0" style="font-weight: 700; font-size: 20px;">New Visitor Check-In</h3>
                        <p class="mb-0" style="font-size: 12px; opacity: 0.8;">Enter the details of the guest arriving at the premises</p>
                    </div>
                </div>

                <form action="{{ route('visitor.store') }}" method="POST" id="checkinForm">
                    @csrf
                    <div class="card-body p-4 p-md-5">
                        
                        <div class="row">
                            <div class="col-md-6 form-group mb-4">
                                <label>Visitor Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="v_name" class="form-control" required placeholder="Enter full name">
                            </div>
                            <div class="col-md-6 form-group mb-4">
                                <label>Phone Number <span class="text-danger">*</span></label>
                                <input type="text" name="phone" id="v_phone" class="form-control" required placeholder="Contact number">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-4">
                                <label>Person to Meet <span class="text-danger">*</span></label>
                                <input type="text" name="person_to_meet" class="form-control" required placeholder="Who are they meeting?">
                            </div>
                            <div class="col-md-6 form-group mb-4">
                                <label>Company / Organization</label>
                                <input type="text" name="company" id="v_company" class="form-control" placeholder="Optional">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 form-group mb-4">
                                <label>Purpose of Visit <span class="text-danger">*</span></label>
                                <input type="text" name="purpose" class="form-control" required placeholder="e.g. Client Meeting, Interview, Delivery">
                            </div>
                        </div>

                    </div>

                    <div class="card-footer text-right p-4" style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; border-radius: 0 0 12px 12px;">
                        <button type="submit" class="btn btn-indigo">
                            <i class="mdi mdi-check-all mr-2"></i> Confirm & Check-In
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection