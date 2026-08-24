@extends('layouts.master') 

@section('content')
<div class="container-fluid mt-4">
    
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-1 text-dark" style="font-weight: 600;">Salary Master Settings</h3>
            <div class="text-muted small fw-bold" style="font-size: 13px;">
                <span class="text-primary">Settings</span> &gt; <span class="text-dark">Salary Master</span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success fw-bold"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius: 10px;">
        <div class="card-header text-white py-3 d-flex justify-content-between align-items-center" style="background-color: #2c3e50; border-top-left-radius: 10px; border-top-right-radius: 10px;">
            <h5 class="mb-0 text-white" style="font-size: 16px;"><i class="fas fa-cog me-2"></i> Department & Designation Base Salary</h5>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Current Salary (₹)</th>
                            <th>Scheduled Salary (Timer)</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salaries as $sal)
                        <tr>
                            <td class="align-middle fw-bold text-secondary">{{ $sal->department }}</td>
                            <td class="align-middle fw-bold text-dark">{{ $sal->designation }}</td>
                            <td class="align-middle fw-bold text-success" style="font-size: 15px;">₹{{ number_format($sal->current_base_salary) }}</td>
                            <td class="align-middle">
                                @if($sal->scheduled_salary)
                                    <div class="text-warning fw-bold"><i class="fas fa-clock me-1"></i> ₹{{ number_format($sal->scheduled_salary) }}</div>
                                    <small class="text-muted fw-bold">Effective from: {{ \Carbon\Carbon::parse($sal->effective_date)->format('d M, Y') }}</small>
                                @else
                                    <span class="badge bg-light text-muted border">No upcoming changes</span>
                                @endif
                            </td>
                            <td class="align-middle text-end">
                                <!-- பட்டன் இப்போது பக்காவாக வேலை செய்யும் (data-toggle and data-bs-toggle added for cross compatibility) -->
                                <button class="btn btn-sm btn-outline-primary fw-bold rounded-pill px-3" data-toggle="modal" data-target="#editSalaryModal{{ $sal->id }}" data-bs-toggle="modal" data-bs-target="#editSalaryModal{{ $sal->id }}">
                                    <i class="fas fa-edit me-1"></i> Update Salary
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- UPDATE SALARY MODALS (MOVED OUTSIDE TABLE) -->
<!-- ========================================== -->
@foreach($salaries as $sal)
<div class="modal fade" id="editSalaryModal{{ $sal->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header text-white" style="background-color: #5867dd;">
                <h5 class="modal-title fw-bold">Update Salary: {{ $sal->designation }}</h5>
                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="background:transparent; border:none; font-size:1.5rem;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('salary.master.update', $sal->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-light">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">New Base Salary (₹)</label>
                        <input type="number" name="new_salary" class="form-control fw-bold" value="{{ $sal->current_base_salary }}" required style="font-size: 18px; color: #5867dd;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Effective Date (Timer)</label>
                        <select name="effective_timer" class="form-control fw-bold form-select" onchange="toggleCustomDate(this, {{ $sal->id }})">
                            <option value="next_month">Next Month 1st (Recommended)</option>
                            <option value="immediate">Immediate (Applies to current payroll)</option>
                            <option value="custom">Custom Date</option>
                        </select>
                        <small class="text-danger mt-1 d-block"><i class="fas fa-info-circle me-1"></i> Prevents confusion with recently processed payrolls.</small>
                    </div>

                    <div class="mb-3" id="customDateDiv{{ $sal->id }}" style="display: none;">
                        <label class="form-label fw-bold text-muted small text-uppercase">Select Custom Date</label>
                        <input type="date" name="custom_date" class="form-control">
                    </div>

                </div>
                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-light fw-bold" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold" style="background-color: #5867dd; border: none;">Set Salary Timer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script>
    function toggleCustomDate(selectElement, id) {
        let customDateDiv = document.getElementById('customDateDiv' + id);
        if(selectElement.value === 'custom') {
            customDateDiv.style.display = 'block';
        } else {
            customDateDiv.style.display = 'none';
        }
    }
</script>
@endsection