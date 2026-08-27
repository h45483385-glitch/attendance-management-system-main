@extends('layouts.master')

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">Master Attendance</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin" class="text-primary font-weight-bold">Home</a></li>
        <li class="breadcrumb-item">Attendance Records</li>
        <li class="breadcrumb-item active text-dark font-weight-bold">Master Attendance</li>
    </ol>
</div>
@endsection

@section('content')

<style>
    .brand-blue-text { color: #0284c7 !important; }
    .brand-green-bg { background-color: #10b981 !important; color: #fff !important; }
    .brand-blue-bg { background-color: #0284c7 !important; color: #fff !important; }
    
    .dept-accordion .card { border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.03); overflow: hidden; }
    .dept-accordion .card-header { background-color: #ffffff; padding: 15px 25px; cursor: pointer; border-bottom: 2px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; transition: background-color 0.3s ease; }
    .dept-accordion .card-header:hover { background-color: #f8fafc; }
    .dept-title { font-size: 18px; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; }
    .dept-accordion .card-header i.toggle-icon { color: #94a3b8; transition: transform 0.3s ease; font-size: 20px; }
    .dept-accordion .card-header.collapsed i.toggle-icon { transform: rotate(-90deg); }
    
    .emp-card { border-bottom: 1px solid #e2e8f0; padding: 15px 20px; background-color: #fafafa; transition: background-color 0.2s; }
    .emp-card:hover { background-color: #f1f5f9; }
    .emp-card:last-child { border-bottom: none; }
    
    .progress-wrapper { width: 150px; margin-right: 20px; text-align: right; }
</style>

<div class="row mt-3">
    <div class="col-12">
        
        <!-- SMART SEARCH BAR ADDED HERE -->
        <div class="mb-4">
            <form action="/attendance" method="GET" class="input-group shadow-sm" style="max-width: 600px; margin: 0 auto;">
                <input type="text" name="employee_query" class="form-control border-primary" placeholder="Search Employee ID or Name to view Calendar Profile..." required>
                <div class="input-group-append">
                    <button class="btn btn-primary px-4 font-weight-bold" type="submit"><i class="ti-search mr-1"></i> Search Employee</button>
                </div>
            </form>
        </div>

        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header brand-green-bg d-flex justify-content-between align-items-center" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h5 class="mb-0 font-weight-bold"><i class="ti-bar-chart-alt mr-2"></i> Department-wise Master Sheet ({{ \Carbon\Carbon::now()->format('F Y') }})</h5>
            </div>
            
            <div class="card-body bg-light p-4">
                
                @php
                    // GROUP EMPLOYEES BY DEPARTMENT (POSITION)
                    $groupedEmployees = $employees->groupBy(function($emp) {
                        return $emp->position ?: 'Unassigned Role';
                    });
                @endphp

                <!-- Accordion For Departments -->
                <div class="accordion dept-accordion" id="departmentAccordion">
                    
                    @foreach ($groupedEmployees as $departmentName => $deptEmployees)
                        @php
                            // Calculate Today's Percentage for this specific Department using pre-fetched array
                            $totalInDept = $deptEmployees->count();
                            $presentToday = 0;
                            
                            foreach($deptEmployees as $emp) {
                                if(in_array($emp->id, $todayPresentEmpIds)) {
                                    $presentToday++;
                                }
                            }
                            
                            $deptPercentage = $totalInDept > 0 ? round(($presentToday / $totalInDept) * 100) : 0;
                            $pctColor = $deptPercentage >= 80 ? 'text-success' : ($deptPercentage >= 50 ? 'text-warning' : 'text-danger');
                        @endphp
                        
                        <div class="card">
                            <!-- DEPARTMENT HEADER -->
                            <div class="card-header collapsed" id="headingDept{{ Str::slug($departmentName) }}" data-toggle="collapse" data-target="#collapseDept{{ Str::slug($departmentName) }}" aria-expanded="false">
                                <div class="dept-title">
                                    <i class="ti-briefcase text-primary mr-3" style="font-size: 24px;"></i>
                                    <div>
                                        {{ $departmentName }} 
                                        <div class="text-muted" style="font-size: 13px; font-weight: 500; margin-top: 4px;">
                                            <i class="ti-user mr-1"></i> {{ $totalInDept }} Employees
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-center">
                                    <!-- Today's Percentage Indicator -->
                                    <div class="progress-wrapper">
                                        <div class="font-weight-bold {{ $pctColor }}" style="font-size: 16px;">
                                            {{ $deptPercentage }}% Present Today
                                        </div>
                                        <div class="progress mt-1" style="height: 6px; border-radius: 10px;">
                                            <div class="progress-bar {{ $deptPercentage >= 80 ? 'bg-success' : ($deptPercentage >= 50 ? 'bg-warning' : 'bg-danger') }}" role="progressbar" style="width: {{ $deptPercentage }}%" aria-valuenow="{{ $deptPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <i class="ti-angle-down toggle-icon ml-3"></i>
                                </div>
                            </div>

                            <!-- DEPARTMENT EMPLOYEES LIST -->
                            <div id="collapseDept{{ Str::slug($departmentName) }}" class="collapse" aria-labelledby="headingDept{{ Str::slug($departmentName) }}" data-parent="#departmentAccordion">
                                <div class="card-body p-0">
                                    
                                    @foreach ($deptEmployees as $empIndex => $employee)
                                        <div class="emp-card">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm mr-3">
                                                        <span class="avatar-title rounded-circle brand-blue-bg font-weight-bold" style="width: 35px; height: 35px; font-size: 14px;">
                                                            {{ substr($employee->name, 0, 1) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 font-weight-bold brand-blue-text">{{ $employee->name }}</h6>
                                                        <span class="text-muted" style="font-size: 12px;">ID: #{{ $employee->id }}</span>
                                                    </div>
                                                </div>
                                                
                                                <!-- BUTTON LINKS TO THE CALENDAR PAGE -->
                                                <a href="/attendance?employee_query={{ $employee->id }}" class="btn btn-sm btn-outline-primary rounded-pill px-4 font-weight-bold shadow-sm">
                                                    <i class="ti-calendar mr-1"></i> View Calendar Log
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach

                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
                <!-- End Main Accordion -->

            </div>
        </div>
    </div>
</div>

@endsection