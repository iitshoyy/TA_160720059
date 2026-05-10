@extends('layouts.app')
@section('title','Employees')
@section('page-title','Employee Management')

@section('content')
<div class="page-header">
    <div><h1>Employees</h1><p>Manage restaurant staff</p></div>
    <button class="btn btn-gold" onclick="openModal('addEmpModal')"><i class="fas fa-plus"></i> Add Employee</button>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Username</th><th>Position</th><th>Phone</th><th>Hire Date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($employees as $emp)
                <tr>
                    <td style="font-weight:600;color:var(--cream);">{{ $emp->user->name ?? '-' }}</td>
                    <td style="color:var(--muted);">{{ $emp->user->username ?? '-' }}</td>
                    <td>{{ $emp->position }}</td>
                    <td>{{ $emp->phone ?? '-' }}</td>
                    <td style="font-size:.82rem;color:var(--muted);">{{ $emp->hire_date ? \Carbon\Carbon::parse($emp->hire_date)->format('d M Y') : '-' }}</td>
                    <td><span class="status {{ $emp->status==='active'?'status-available':'status-cancelled' }}">{{ ucfirst($emp->status) }}</span></td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <button class="btn btn-outline btn-sm" onclick='openEditEmp({{ json_encode($emp) }})'><i class="fas fa-edit"></i></button>
                            <form method="POST" action="{{ route('employees.destroy',$emp->id) }}">@csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Remove this employee?')"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:40px;">No employees yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px;">{{ $employees->links() }}</div>
</div>

<div class="modal-overlay" id="addEmpModal">
    <div class="modal">
        <div class="modal-header"><div class="modal-title">Add Employee</div><button class="modal-close" onclick="closeModal('addEmpModal')"><i class="fas fa-times"></i></button></div>
        <form method="POST" action="{{ route('employees.store') }}">@csrf
        <div class="modal-body">
            <div class="form-group"><label class="form-label">User Account</label>
                <select name="users_id" class="form-control" required>
                    <option value="">Select user</option>
                    @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }} (@{{ $u->username }})</option>@endforeach
                </select>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Position</label>
                    <select name="position" class="form-control" required>
                        <option value="Kasir">Kasir</option>
                        <option value="Chef">Chef</option>
                        <option value="Manager">Manager</option>
                        <option value="Cleaning">Cleaning Staff</option>
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Phone</label><input name="phone" class="form-control" placeholder="08xx-xxxx"></div>
            </div>
            <div class="form-group"><label class="form-label">Hire Date</label><input type="date" name="hire_date" class="form-control"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('addEmpModal')">Cancel</button>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Add Employee</button>
        </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="editEmpModal">
    <div class="modal">
        <div class="modal-header"><div class="modal-title">Edit Employee</div><button class="modal-close" onclick="closeModal('editEmpModal')"><i class="fas fa-times"></i></button></div>
        <form method="POST" id="editEmpForm">@csrf @method('PUT')
        <div class="modal-body">
            <div class="form-row">
                <div class="form-group"><label class="form-label">Position</label>
                    <select name="position" id="eePos" class="form-control">
                        <option value="Kasir">Kasir</option>
                        <option value="Chef">Chef</option><option value="Manager">Manager</option>
                        <option value="Cleaning">Cleaning Staff</option>
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Phone</label><input name="phone" id="eePhone" class="form-control"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Hire Date</label><input type="date" name="hire_date" id="eeHire" class="form-control"></div>
                <div class="form-group"><label class="form-label">Status</label>
                    <select name="status" id="eeStat" class="form-control"><option value="active">Active</option><option value="inactive">Inactive</option></select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('editEmpModal')">Cancel</button>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Update</button>
        </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
function openEditEmp(e) {
    document.getElementById('editEmpForm').action = '/employees/' + e.id;
    document.getElementById('eePos').value   = e.position;
    document.getElementById('eePhone').value = e.phone || '';
    document.getElementById('eeHire').value  = e.hire_date || '';
    document.getElementById('eeStat').value  = e.status;
    openModal('editEmpModal');
}
</script>
@endpush
