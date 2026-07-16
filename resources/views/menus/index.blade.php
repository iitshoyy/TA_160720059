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
            <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Availability</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($menus as $menu)
                <tr>
                    <td>
                        @if($menu->image)
                        <img src="{{ asset('storage/'.$menu->image) }}" alt="{{ $menu->name }}" class="menu-thumb">
                        @else
                        <span class="menu-thumb menu-thumb-empty"><i class="fas fa-utensils"></i></span>
                        @endif
                    </td>
                    <td class="fw-500 text-cream">{{ $menu->name }}</td>
                    <td>{{ $menu->category->name ?? '-' }}</td>
                    <td class="text-gold fw-600">Rp {{ number_format($menu->price) }}</td>
                    <td>
                        @php($cap = $menu->stockCapacity())
                        @if($menu->components->isEmpty())
                            <span class="status status-cancelled">No recipe</span>
                        @elseif($cap > 0)
                            <span class="status status-available">Available ({{ $cap }})</span>
                        @else
                            <span class="status status-cancelled">Sold Out</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <button class="btn btn-outline btn-sm" title="Recipe" onclick="openRecipe({{ $menu->id }}, '{{ addslashes($menu->name) }}')"><i class="fas fa-flask"></i></button>
                            <button class="btn btn-outline btn-sm" onclick='openEdit({{ json_encode($menu) }})'><i class="fas fa-edit"></i></button>
                            <form method="POST" action="{{ route('menus.destroy',$menu->id) }}">@csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this menu item?')"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <x-empty-state colspan="6" icon="fas fa-utensils" message="No menu items found" />
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
        <form method="POST" action="{{ route('menus.store') }}" enctype="multipart/form-data">@csrf
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
            <div class="form-group">
                <label class="form-label">Photo</label>
                <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(this,'addImgPreview')">
                <div class="img-preview" id="addImgPreview" style="display:none;"><img src="" alt="preview"></div>
                <small style="color:var(--muted);font-size:.75rem;">JPG/PNG, max 2 MB. Square photos look best.</small>
            </div>
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
        <form method="POST" id="editMenuForm" enctype="multipart/form-data">@csrf @method('PUT')
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
            <div class="form-group">
                <label class="form-label">Photo</label>
                <div class="img-preview" id="editImgPreview" style="display:none;"><img src="" alt="preview"></div>
                <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(this,'editImgPreview')">
                <small style="color:var(--muted);font-size:.75rem;">Upload a new file to replace the current photo. Square photos look best.</small>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('editMenuModal')">Cancel</button>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Update</button>
        </div>
        </form>
    </div>
</div>
<!-- Recipe Modal -->
<div class="modal-overlay" id="recipeModal">
    <div class="modal">
        <div class="modal-header"><div class="modal-title" id="recipeTitle">Recipe</div><button class="modal-close" onclick="closeModal('recipeModal')"><i class="fas fa-times"></i></button></div>
        <form method="POST" id="recipeForm">@csrf @method('PUT')
        <div class="modal-body">
            <p style="color:var(--muted);font-size:0.82rem;margin-bottom:10px;">A menu is orderable only when it has a recipe and every ingredient is in stock.</p>
            <div id="recipeRows"></div>
            <button type="button" class="btn btn-outline btn-sm" onclick="addRecipeRow()"><i class="fas fa-plus"></i> Add ingredient</button>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('recipeModal')">Cancel</button>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save Recipe</button>
        </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
const STORAGE_BASE = "{{ asset('storage') }}/";

function openEdit(m) {
    document.getElementById('editMenuForm').action = '/menus/' + m.id;
    document.getElementById('editName').value     = m.name;
    document.getElementById('editPrice').value    = m.price;
    document.getElementById('editDesc').value     = m.description || '';
    document.getElementById('editCategory').value = m.categoryMenus_id;

    const prev = document.getElementById('editImgPreview');
    const fileInput = prev.nextElementSibling;
    fileInput.value = '';
    if (m.image) {
        prev.querySelector('img').src = STORAGE_BASE + m.image;
        prev.style.display = '';
    } else {
        prev.style.display = 'none';
    }
    openModal('editMenuModal');
}

function previewImage(input, previewId) {
    const prev = document.getElementById(previewId);
    const file = input.files && input.files[0];
    if (!file) { return; }
    prev.querySelector('img').src = URL.createObjectURL(file);
    prev.style.display = '';
}

const INGREDIENTS = @json($ingredients);

function ingredientOptions(selected) {
    return INGREDIENTS.map(i =>
        `<option value="${i.id}" ${i.id == selected ? 'selected' : ''}>${i.name} (${i.unit})</option>`
    ).join('');
}

function addRecipeRow(ingId = '', qty = '') {
    const row = document.createElement('div');
    row.className = 'form-row';
    row.style.cssText = 'display:flex;gap:8px;margin-bottom:8px;align-items:center;';
    row.innerHTML = `
        <select name="ingredients[]" class="form-control" required style="flex:2;">
            <option value="">Select ingredient</option>${ingredientOptions(ingId)}
        </select>
        <input type="number" name="quantities[]" class="form-control" step="0.0001" min="0.0001" required placeholder="Qty per portion" value="${qty}" style="flex:1;">
        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>`;
    document.getElementById('recipeRows').appendChild(row);
}

async function openRecipe(id, name) {
    document.getElementById('recipeTitle').textContent = 'Recipe — ' + name;
    document.getElementById('recipeForm').action = '/menus/' + id + '/recipe';
    document.getElementById('recipeRows').innerHTML = '';
    const resp = await fetch('/menus/' + id + '/recipe', { headers: { 'Accept': 'application/json' } });
    const components = await resp.json();
    if (components.length === 0) { addRecipeRow(); }
    else { components.forEach(c => addRecipeRow(c.ingridients_id, c.quantity)); }
    openModal('recipeModal');
}
</script>
@endpush
@push('styles')
<style>
    .menu-thumb{width:44px;height:44px;object-fit:cover;border:1px solid var(--border);border-radius:4px;display:inline-block;}
    .menu-thumb-empty{display:inline-flex;align-items:center;justify-content:center;color:var(--muted);background:rgba(255,255,255,.03);}
    .img-preview{margin:8px 0;}
    .img-preview img{width:96px;height:96px;object-fit:cover;border:1px solid var(--border);border-radius:6px;}
</style>
@endpush
