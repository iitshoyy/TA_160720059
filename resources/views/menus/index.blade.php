@extends('layouts.app')
@section('title','Menu')
@section('page-title','Menu Management')

@section('content')
<div class="page-header">
    <div><h1>Menu Items</h1><p>Manage food and beverage menu</p></div>
    <button class="btn btn-gold" onclick="openModal('addMenuModal')"><i class="fas fa-plus"></i> Add Item</button>
</div>

<div class="card" style="margin-bottom:20px;">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div class="form-group" style="margin:0;flex:1;min-width:160px;"><label class="form-label">Search</label><input name="search" class="form-control" placeholder="Search menu..." value="{{ request('search') }}"></div>
        <div class="form-group" style="margin:0;"><label class="form-label">Category</label>
            <select name="category" class="form-control">
                <option value="">All Categories</option>
                @foreach($categories as $c)<option value="{{ $c->id }}" {{ request('category')==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-outline"><i class="fas fa-filter"></i> Filter</button>
        <a href="{{ route('menus.index') }}" class="btn btn-outline"><i class="fas fa-times"></i> Clear</a>
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Availability</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($menus as $menu)
                <tr>
                    <td style="font-weight:500;color:var(--cream);">{{ $menu->name }}</td>
                    <td>{{ $menu->category->name ?? '-' }}</td>
                    <td style="color:var(--gold);font-weight:600;">Rp {{ number_format($menu->price) }}</td>
                    <td>
                        <form method="POST" action="{{ route('menus.toggle',$menu->id) }}">@csrf @method('PATCH')
                            <button type="submit" class="status {{ $menu->availability?'status-available':'status-cancelled' }}" style="background:none;border:none;cursor:pointer;padding:3px 10px;">
                                {{ $menu->availability?'Available':'Unavailable' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <button class="btn btn-outline btn-sm" onclick='openEdit({{ json_encode($menu) }})'><i class="fas fa-edit"></i></button>
                            <form method="POST" action="{{ route('menus.destroy',$menu->id) }}">@csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this menu item?')"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:40px;">No menu items found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px;">{{ $menus->withQueryString()->links() }}</div>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addMenuModal">
    <div class="modal">
        <div class="modal-header"><div class="modal-title">Add Menu Item</div><button class="modal-close" onclick="closeModal('addMenuModal')"><i class="fas fa-times"></i></button></div>
        <form method="POST" action="{{ route('menus.store') }}">@csrf
        <div class="modal-body">
            <div class="form-group"><label class="form-label">Name</label><input name="name" class="form-control" required placeholder="e.g. Nasi Goreng"></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Category</label>
                    <select name="categoryMenus_id" class="form-control" required>
                        <option value="">Select category</option>
                        @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Price (Rp)</label><input type="number" name="price" class="form-control" required placeholder="0"></div>
            </div>
            <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2" placeholder="Optional description..."></textarea></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('addMenuModal')">Cancel</button>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save</button>
        </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editMenuModal">
    <div class="modal">
        <div class="modal-header"><div class="modal-title">Edit Menu Item</div><button class="modal-close" onclick="closeModal('editMenuModal')"><i class="fas fa-times"></i></button></div>
        <form method="POST" id="editMenuForm">@csrf @method('PUT')
        <div class="modal-body">
            <div class="form-group"><label class="form-label">Name</label><input name="name" id="editName" class="form-control" required></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Category</label>
                    <select name="categoryMenus_id" id="editCategory" class="form-control" required>
                        @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Price (Rp)</label><input type="number" name="price" id="editPrice" class="form-control" required></div>
            </div>
            <div class="form-group"><label class="form-label">Description</label><textarea name="description" id="editDesc" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('editMenuModal')">Cancel</button>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Update</button>
        </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
function openEdit(m) {
    document.getElementById('editMenuForm').action = '/menus/' + m.id;
    document.getElementById('editName').value     = m.name;
    document.getElementById('editPrice').value    = m.price;
    document.getElementById('editDesc').value     = m.description || '';
    document.getElementById('editCategory').value = m.categoryMenus_id;
    openModal('editMenuModal');
}
</script>
@endpush
