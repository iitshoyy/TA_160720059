@extends('layouts.app')
@section('title','Tables')
@section('page-title','Table Management')

@section('content')
<div class="page-header">
    <div><h1>Tables</h1><p>Manage dining tables and generate QR codes</p></div>
    <a href="{{ route('tables.qr-sheet') }}" class="btn btn-outline" target="_blank"><i class="fas fa-qrcode"></i> Print all QR codes</a>
    <button class="btn btn-gold" onclick="openModal('addTableModal')"><i class="fas fa-plus"></i> Add Table</button>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;">
    @forelse($tables as $table)
    <div style="background:var(--surface);border:1px solid var(--border);border-left:3px solid {{ $table->status==='available'?'#27ae60':($table->status==='occupied'?'#c0392b':'#2980b9') }};padding:16px;position:relative;">
        <div style="font-size:1.8rem;text-align:center;margin-bottom:6px;">🪑</div>
        <div style="font-size:1rem;color:var(--cream);text-align:center;font-weight:600;">{{ $table->name }}</div>
        <div style="text-align:center;color:var(--muted);font-size:.8rem;margin-bottom:10px;">Capacity: {{ $table->capacity }} pax</div>
        <div style="text-align:center;margin-bottom:14px;"><span class="status status-{{ $table->status }}">{{ ucfirst($table->status) }}</span></div>
        <div style="display:flex;gap:6px;flex-direction:column;">
            <a href="{{ route('tables.qr',$table->id) }}" class="btn btn-gold btn-sm" style="width:100%;justify-content:center;" target="_blank"><i class="fas fa-qrcode"></i> QR Code</a>
            <button class="btn btn-outline btn-sm" style="width:100%;justify-content:center;" onclick='openEditTable({{ json_encode($table) }})'><i class="fas fa-edit"></i> Edit</button>
            <form method="POST" action="{{ route('tables.destroy',$table->id) }}">@csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" style="width:100%;justify-content:center;" onclick="return confirm('Delete this table?')"><i class="fas fa-trash"></i> Delete</button>
            </form>
        </div>
    </div>
    @empty
    <div style="grid-column:1/-1;text-align:center;color:var(--muted);padding:60px;">No tables configured. Add your first table!</div>
    @endforelse
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addTableModal">
    <div class="modal">
        <div class="modal-header"><div class="modal-title">Add Table</div><button class="modal-close" onclick="closeModal('addTableModal')"><i class="fas fa-times"></i></button></div>
        <form method="POST" action="{{ route('tables.store') }}">@csrf
        <div class="modal-body">
            <div class="form-row">
                <div class="form-group"><label class="form-label">Table Name</label><input name="name" class="form-control" required placeholder="e.g. Table A1, VIP 1"></div>
                <div class="form-group"><label class="form-label">Capacity (pax)</label><input type="number" name="capacity" class="form-control" required min="1" placeholder="4"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('addTableModal')">Cancel</button>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Add Table</button>
        </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editTableModal">
    <div class="modal">
        <div class="modal-header"><div class="modal-title">Edit Table</div><button class="modal-close" onclick="closeModal('editTableModal')"><i class="fas fa-times"></i></button></div>
        <form method="POST" id="editTableForm">@csrf @method('PUT')
        <div class="modal-body">
            <div class="form-row">
                <div class="form-group"><label class="form-label">Name</label><input name="name" id="etName" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Capacity</label><input type="number" name="capacity" id="etCap" class="form-control" required></div>
            </div>
            <div class="form-group"><label class="form-label">Status</label>
                <select name="status" id="etStatus" class="form-control">
                    <option value="available">Available</option>
                    <option value="occupied">Occupied</option>
                    <option value="reserved">Reserved</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('editTableModal')">Cancel</button>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Update</button>
        </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
function openEditTable(t) {
    document.getElementById('editTableForm').action = '/tables/' + t.id;
    document.getElementById('etName').value   = t.name;
    document.getElementById('etCap').value    = t.capacity;
    document.getElementById('etStatus').value = t.status;
    openModal('editTableModal');
}
</script>
@endpush
