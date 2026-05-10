@extends('layouts.app')
@section('title','Suppliers')
@section('page-title','Supplier Management')

@section('content')
<div class="page-header">
    <div><h1>Suppliers</h1><p>Manage ingredient suppliers and vendors</p></div>
    <button class="btn btn-gold" onclick="openModal('addSupplierModal')"><i class="fas fa-plus"></i> Add Supplier</button>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Supplier</th><th>Phone</th><th>Email</th><th>Address</th><th>Ingredients</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($suppliers as $s)
                <tr>
                    <td style="font-weight:600;color:var(--cream);">{{ $s->name }}</td>
                    <td>{{ $s->phone ?? '-' }}</td>
                    <td>{{ $s->email ?? '-' }}</td>
                    <td style="max-width:180px;font-size:.82rem;color:var(--muted);">{{ Str::limit($s->address,50) ?? '-' }}</td>
                    <td><span class="badge-pill">{{ $s->ingridients_count }}</span></td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <button class="btn btn-outline btn-sm" onclick='openEditSupplier({{ json_encode($s) }})'><i class="fas fa-edit"></i></button>
                            <form method="POST" action="{{ route('suppliers.destroy',$s->id) }}">@csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this supplier?')"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:40px;">No suppliers yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px;">{{ $suppliers->links() }}</div>
</div>

<div class="modal-overlay" id="addSupplierModal">
    <div class="modal">
        <div class="modal-header"><div class="modal-title">Add Supplier</div><button class="modal-close" onclick="closeModal('addSupplierModal')"><i class="fas fa-times"></i></button></div>
        <form method="POST" action="{{ route('suppliers.store') }}">@csrf
        <div class="modal-body">
            <div class="form-group"><label class="form-label">Supplier Name</label><input name="name" class="form-control" required placeholder="Company or person name"></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Phone</label><input name="phone" class="form-control" placeholder="08xx-xxxx-xxxx"></div>
                <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-control" placeholder="supplier@email.com"></div>
            </div>
            <div class="form-group"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2" placeholder="Full address..."></textarea></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('addSupplierModal')">Cancel</button>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save</button>
        </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="editSupplierModal">
    <div class="modal">
        <div class="modal-header"><div class="modal-title">Edit Supplier</div><button class="modal-close" onclick="closeModal('editSupplierModal')"><i class="fas fa-times"></i></button></div>
        <form method="POST" id="editSupplierForm">@csrf @method('PUT')
        <div class="modal-body">
            <div class="form-group"><label class="form-label">Name</label><input name="name" id="esName" class="form-control" required></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Phone</label><input name="phone" id="esPhone" class="form-control"></div>
                <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" id="esEmail" class="form-control"></div>
            </div>
            <div class="form-group"><label class="form-label">Address</label><textarea name="address" id="esAddr" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('editSupplierModal')">Cancel</button>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Update</button>
        </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
function openEditSupplier(s) {
    document.getElementById('editSupplierForm').action = '/suppliers/' + s.id;
    document.getElementById('esName').value  = s.name;
    document.getElementById('esPhone').value = s.phone || '';
    document.getElementById('esEmail').value = s.email || '';
    document.getElementById('esAddr').value  = s.address || '';
    openModal('editSupplierModal');
}
</script>
@endpush
