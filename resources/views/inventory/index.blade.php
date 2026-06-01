@extends('layouts.app')
@section('title', 'Inventory')
@section('page-title', 'Inventory & Stock')

@section('content')
<div class="page-header">
    <div>
        <h1>Inventory</h1>
        <p>Manage raw materials and ingredient stock</p>
    </div>
    <button class="btn btn-gold" onclick="openModal('addIngredientModal')"><i class="fas fa-plus"></i> Add Ingredient</button>
</div>

<div class="stat-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <div class="stat-label">Total Ingredients</div>
        <div class="stat-value">{{ $totalIngredients ?? 0 }}</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-label">Low Stock</div>
        <div class="stat-value">{{ $lowStock ?? 0 }}</div>
        <div class="stat-sub">Below minimum level</div>
    </div>
    <div class="stat-card success">
        <div class="stat-label">Sufficient</div>
        <div class="stat-value">{{ $sufficientStock ?? 0 }}</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-label">Out of Stock</div>
        <div class="stat-value">{{ $outOfStock ?? 0 }}</div>
    </div>
</div>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px;">
        <div style="display:flex; gap:8px;">
            <select class="form-control" onchange="filterType(this.value)" style="width:auto;">
                <option value="">All Types</option>
                @foreach($ingredientTypes ?? [] as $type)
                <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <input type="text" class="form-control" placeholder="Search ingredients..." oninput="filterSearch(this.value)" style="width:220px;">
    </div>
    <div class="table-wrap">
        <table id="inventoryTable">
            <thead>
                <tr>
                    <th>Ingredient</th>
                    <th>Type</th>
                    <th>Unit</th>
                    <th>Stock</th>
                    <th>Min Stock</th>
                    <th>Cost/Unit</th>
                    <th>Supplier</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ingredients ?? [] as $ingredient)
                @php
                    $stock = $ingredient->inventories->first();
                    $qty = $stock ? $stock->quantity_on_hand : 0;
                    $min = $ingredient->min_stock ?? 10;
                    $stockStatus = $qty <= 0 ? 'danger' : ($qty <= $min ? 'low' : 'available');
                    $stockLabel = $qty <= 0 ? 'Out of Stock' : ($qty <= $min ? 'Low Stock' : 'Sufficient');
                @endphp
                <tr class="inv-row" data-type="{{ $ingredient->ingridient_types_id }}">
                    <td class="fw-500 text-cream">{{ $ingredient->name }}</td>
                    <td>{{ $ingredient->type->name ?? '-' }}</td>
                    <td>{{ $ingredient->unit ?? 'pcs' }}</td>
                    <td style="font-weight:600;">{{ number_format($qty, 2) }}</td>
                    <td class="text-muted">{{ $min }}</td>
                    <td>Rp {{ number_format($ingredient->cost_per_unit) }}</td>
                    <td>{{ $ingredient->supplier->name ?? '-' }}</td>
                    <td><span class="status status-{{ $stockStatus }}">{{ $stockLabel }}</span></td>
                    <td style="font-size:0.78rem; color:var(--muted);">{{ $stock ? \Carbon\Carbon::parse($stock->last_updated)->format('d/m/y') : '-' }}</td>
                    <td>
                        <div style="display:flex; gap:6px;">
                            <button class="btn btn-outline btn-sm" onclick="openAdjust({{ $ingredient->id }}, '{{ $ingredient->name }}', {{ $qty }})">
                                <i class="fas fa-edit"></i> Adjust
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="confirmDelete({{ $ingredient->id }})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <x-empty-state colspan="10" icon="fas fa-box-open" message="No ingredients found. Add your first ingredient!" />
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($ingredients) && method_exists($ingredients, 'hasPages') && $ingredients->hasPages())
    <div style="margin-top:16px;">{{ $ingredients->links() }}</div>
    @endif
</div>

<!-- Add Ingredient Modal -->
<div class="modal-overlay" id="addIngredientModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Add Ingredient</div>
            <button class="modal-close" onclick="closeModal('addIngredientModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('inventory.store') }}">
        @csrf
        <div class="modal-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Ingredient Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Chicken Breast">
                </div>
                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select name="ingridient_types_id" class="form-control" required>
                        <option value="">Select Type</option>
                        @foreach($ingredientTypes ?? [] as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Unit</label>
                    <select name="unit" class="form-control">
                        <option value="kg">kg</option>
                        <option value="g">gram</option>
                        <option value="liter">liter</option>
                        <option value="ml">ml</option>
                        <option value="pcs">pcs</option>
                        <option value="pack">pack</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Cost Per Unit (Rp)</label>
                    <input type="number" name="cost_per_unit" class="form-control" required placeholder="0">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Initial Stock</label>
                    <input type="number" name="initial_stock" class="form-control" step="0.01" placeholder="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Minimum Stock</label>
                    <input type="number" name="min_stock" class="form-control" step="0.01" placeholder="10">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Supplier</label>
                <select name="supplier_id" class="form-control">
                    <option value="">No Supplier</option>
                    @foreach($suppliers ?? [] as $sup)
                    <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('addIngredientModal')">Cancel</button>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save Ingredient</button>
        </div>
        </form>
    </div>
</div>

<!-- Adjust Stock Modal -->
<div class="modal-overlay" id="adjustModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Adjust Stock: <span id="adjustName"></span></div>
            <button class="modal-close" onclick="closeModal('adjustModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('inventory.adjust') }}">
        @csrf
        <input type="hidden" name="ingredient_id" id="adjustIngredientId">
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Current Stock</label>
                <input type="text" id="currentStockDisplay" class="form-control" readonly class="text-gold">
            </div>
            <div class="form-group">
                <label class="form-label">Adjustment Type</label>
                <select name="adjustment_type" class="form-control">
                    <option value="add">Add Stock (+)</option>
                    <option value="subtract">Use / Remove Stock (−)</option>
                    <option value="set">Set Exact Amount</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-control" step="0.01" required placeholder="0">
            </div>
            <div class="form-group">
                <label class="form-label">Reason / Notes</label>
                <input type="text" name="notes" class="form-control" placeholder="e.g. Restock from supplier, Damaged goods...">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('adjustModal')">Cancel</button>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save Adjustment</button>
        </div>
        </form>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;">@csrf @method('DELETE')</form>
@endsection

@push('scripts')
<script>
function openAdjust(id, name, currentStock) {
    document.getElementById('adjustIngredientId').value = id;
    document.getElementById('adjustName').textContent = name;
    document.getElementById('currentStockDisplay').value = currentStock;
    openModal('adjustModal');
}
function filterSearch(q) {
    document.querySelectorAll('.inv-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q.toLowerCase()) ? '' : 'none';
    });
}
function filterType(typeId) {
    document.querySelectorAll('.inv-row').forEach(row => {
        row.style.display = !typeId || row.dataset.type == typeId ? '' : 'none';
    });
}
function confirmDelete(id) {
    if (confirm('Delete this ingredient? This cannot be undone.')) {
        const form = document.getElementById('deleteForm');
        form.action = `/inventory/${id}`;
        form.submit();
    }
}
</script>
@endpush
