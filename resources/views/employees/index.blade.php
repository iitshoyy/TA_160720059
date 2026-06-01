@extends('layouts.app')
@section('title','Employees')
@section('page-title','Employee Management')

@section('content')
<div class="page-header">
    <div><h1>Employees</h1><p>Create and manage cashier and kitchen accounts</p></div>
    <button class="btn btn-gold" onclick="openModal('addEmpModal')"><i class="fas fa-user-plus"></i> Add Employee</button>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Position</th>
                    <th>Phone</th>
                    <th>Hire Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                <tr>
                    <td class="fw-600 text-cream">{{ $emp->user->name ?? '-' }}</td>
                    <td class="text-muted">{{ $emp->user->username ?? '-' }}</td>
                    <td>
                        @php $roleName = $emp->user->role->name ?? '—'; @endphp
                        <span class="role-badge role-{{ strtolower($roleName) }}">{{ $roleName }}</span>
                    </td>
                    <td>{{ $emp->position }}</td>
                    <td>{{ $emp->phone ?? '-' }}</td>
                    <td style="font-size:.82rem;color:var(--muted);">{{ $emp->hire_date ? \Carbon\Carbon::parse($emp->hire_date)->format('d M Y') : '-' }}</td>
                    <td><span class="status {{ $emp->status==='active'?'status-available':'status-cancelled' }}">{{ ucfirst($emp->status) }}</span></td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <button class="btn btn-outline btn-sm" onclick='openEditEmp(@json($emp->load("user.role")))'><i class="fas fa-edit"></i></button>
                            <form method="POST" action="{{ route('employees.destroy',$emp->id) }}">@csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Remove this employee and their login account?')"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <x-empty-state colspan="8" icon="fas fa-user-group" message="No employees yet" />
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px;">{{ $employees->links() }}</div>
</div>

<!-- Add Employee Modal -->
<div class="modal-overlay" id="addEmpModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Add Employee</div>
            <button class="modal-close" onclick="closeModal('addEmpModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('employees.store') }}">@csrf
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input name="name" class="form-control" required placeholder="e.g. Siti Aminah" value="{{ old('name') }}">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input name="username" class="form-control" required placeholder="login id" value="{{ old('username') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control" required minlength="6" placeholder="min 6 chars">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Role *</label>
                    <select name="roles_id" class="form-control" required>
                        <option value="">Select role</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}" {{ old('roles_id')==$r->id?'selected':'' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Position *</label>
                    <input name="position" class="form-control" required placeholder="e.g. Kasir, Sous Chef" value="{{ old('position') }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input name="phone" class="form-control" placeholder="08xx-xxxx-xxxx" value="{{ old('phone') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Hire Date</label>
                    <input type="date" name="hire_date" class="form-control" value="{{ old('hire_date') }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Email (optional)</label>
                <input type="email" name="email" class="form-control" placeholder="Leave blank to auto-generate" value="{{ old('email') }}">
            </div>
            @if($errors->any())
                <div class="alert alert-error" style="margin-top:8px;">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                    </div>
                </div>
            @endif
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('addEmpModal')">Cancel</button>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Create Account</button>
        </div>
        </form>
    </div>
</div>

<!-- Edit Employee Modal -->
<div class="modal-overlay" id="editEmpModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Edit Employee <span id="eeUsername" style="color:var(--gold);"></span></div>
            <button class="modal-close" onclick="closeModal('editEmpModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="editEmpForm">@csrf @method('PUT')
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input name="name" id="eeName" class="form-control" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="roles_id" id="eeRole" class="form-control" required>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Position</label>
                    <input name="position" id="eePos" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input name="phone" id="eePhone" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Hire Date</label>
                    <input type="date" name="hire_date" id="eeHire" class="form-control">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="eeStat" class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Reset Password</label>
                    <input type="password" name="password" id="eePass" class="form-control" minlength="6" placeholder="Leave blank to keep current">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('editEmpModal')">Cancel</button>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save Changes</button>
        </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEditEmp(e) {
    document.getElementById('editEmpForm').action = '/employees/' + e.id;
    document.getElementById('eeUsername').textContent = e.user?.username ? '@' + e.user.username : '';
    document.getElementById('eeName').value  = e.user?.name ?? '';
    document.getElementById('eeRole').value  = e.user?.roles_id ?? '';
    document.getElementById('eePos').value   = e.position ?? '';
    document.getElementById('eePhone').value = e.phone ?? '';
    document.getElementById('eeHire').value  = (e.hire_date ?? '').substring(0, 10);
    document.getElementById('eeStat').value  = e.status ?? 'active';
    document.getElementById('eePass').value  = '';
    openModal('editEmpModal');
}
@if($errors->any())
    // Re-open add modal if validation failed so the user sees their input + errors.
    openModal('addEmpModal');
@endif
</script>
@endpush
