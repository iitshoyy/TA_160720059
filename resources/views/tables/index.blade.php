@extends('layouts.app')
@section('title','Tables')
@section('page-title','Table Management')

@section('content')
<div class="page-header">
    <div><h1>Tables</h1><p>Manage dining tables and generate QR codes</p></div>
    <a href="{{ route('tables.qr-sheet') }}" class="btn btn-outline" target="_blank"><i class="fas fa-qrcode"></i> Print all QR codes</a>
    <button class="btn btn-gold" onclick="openModal('addTableModal')"><i class="fas fa-plus"></i> Add Table</button>
</div>

@php
    $statusColor = fn ($s) => $s === 'available' ? '#27ae60' : ($s === 'occupied' ? '#c0392b' : '#2980b9');
    // A table is placed only when it has a floor AND both coordinates.
    $isPlaced = fn ($t) => ! is_null($t->floor_id) && ! is_null($t->pos_x) && ! is_null($t->pos_y);
    $fpUnplaced = $tables->filter(fn ($t) => ! $isPlaced($t));
    $placedByFloor = $tables->filter($isPlaced)->groupBy('floor_id');
@endphp

<div class="fp-section">
    <div class="fp-bar">
        <div>
            <h2 class="fp-title">Floor Plan</h2>
            <p class="fp-sub">Pick a floor, then drag tables onto its grid. Drag a table back to the tray to unplace it.</p>
        </div>
        <div class="fp-actions">
            <span id="fpDirty" class="fp-dirty">Unsaved changes</span>
            <button class="btn btn-gold" id="fpSave"><i class="fas fa-save"></i> Save Layout</button>
        </div>
    </div>

    <div class="fp-tabs" id="fpTabs">
        @foreach($floors as $i => $floor)
        <div class="fp-tab {{ $i === 0 ? 'active' : '' }}" data-floor="{{ $floor->id }}">
            <span class="fp-tab-name">{{ $floor->name }}</span>
            <form method="POST" action="{{ route('floors.destroy', $floor->id) }}" class="fp-tab-del-form" onsubmit="return confirm('Delete floor &quot;{{ $floor->name }}&quot;? It must have no tables placed on it.')">
                @csrf @method('DELETE')
                <button type="submit" class="fp-tab-del" title="Delete floor"><i class="fas fa-times"></i></button>
            </form>
        </div>
        @endforeach
        <button type="button" class="fp-tab-add" onclick="openModal('addFloorModal')"><i class="fas fa-plus"></i> Add Floor</button>
    </div>

    <div class="fp-stage">
        <div class="fp-tray" id="fpTray" data-drop="tray">
            <div class="fp-tray-title">Unplaced</div>
            @foreach($fpUnplaced as $t)
            <div class="fp-chip" draggable="true" data-id="{{ $t->id }}" data-cap="{{ $t->capacity }}" style="border-left-color:{{ $statusColor($t->status) }}">
                <span class="fp-chip-name">{{ $t->name }}</span>
                <span class="fp-chip-cap">👥 {{ $t->capacity }}</span>
            </div>
            @endforeach
            <div class="fp-tray-empty" {{ $fpUnplaced->count() ? 'style=display:none' : '' }}>All tables placed</div>
        </div>

        <div class="fp-grid-scroll">
            @forelse($floors as $i => $floor)
                @php
                    $floorTables = $placedByFloor->get($floor->id, collect());
                    $fpRows = max(6, ($floorTables->max('pos_y') ?? -1) + 2);
                    $cellMap = [];
                    foreach ($floorTables as $t) { $cellMap[$t->pos_x.','.$t->pos_y] = $t; }
                @endphp
                <div class="fp-grid" data-floor="{{ $floor->id }}" style="{{ $i === 0 ? '' : 'display:none' }}">
                    @for($y = 0; $y < $fpRows; $y++)
                        @for($x = 0; $x < 12; $x++)
                        <div class="fp-cell" data-x="{{ $x }}" data-y="{{ $y }}">
                            @isset($cellMap["$x,$y"])
                            @php $t = $cellMap["$x,$y"]; @endphp
                            <div class="fp-chip" draggable="true" data-id="{{ $t->id }}" data-cap="{{ $t->capacity }}" data-x="{{ $x }}" data-y="{{ $y }}" data-floor="{{ $floor->id }}" style="border-left-color:{{ $statusColor($t->status) }}">
                                <span class="fp-chip-name">{{ $t->name }}</span>
                                <span class="fp-chip-cap">👥 {{ $t->capacity }}</span>
                            </div>
                            @endisset
                        </div>
                        @endfor
                    @endfor
                </div>
            @empty
                <div class="fp-no-floor">No floors yet. Click “Add Floor” to create one.</div>
            @endforelse
        </div>
    </div>
</div>

<h2 class="fp-title" style="margin-bottom:12px;">All Tables</h2>
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

<!-- Add Floor Modal -->
<div class="modal-overlay" id="addFloorModal">
    <div class="modal">
        <div class="modal-header"><div class="modal-title">Add Floor</div><button class="modal-close" onclick="closeModal('addFloorModal')"><i class="fas fa-times"></i></button></div>
        <form method="POST" action="{{ route('floors.store') }}">@csrf
        <div class="modal-body">
            <div class="form-group"><label class="form-label">Floor Name</label><input name="name" class="form-control" required maxlength="50" placeholder="e.g. Lantai 1, Rooftop, Outdoor"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('addFloorModal')">Cancel</button>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Add Floor</button>
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
<script>
(function () {
    const tray    = document.getElementById('fpTray');
    const saveBtn = document.getElementById('fpSave');
    const dirtyEl = document.getElementById('fpDirty');
    const tabs    = document.getElementById('fpTabs');
    const grids   = Array.from(document.querySelectorAll('.fp-grid'));
    if (!grids.length || !tray) return;

    const CSRF     = document.querySelector('meta[name=csrf-token]').content;
    const SAVE_URL = "{{ route('tables.layout') }}";
    let dragged = null;

    function markDirty() { dirtyEl.classList.add('show'); }
    function syncTrayEmpty() {
        const empty = tray.querySelector('.fp-tray-empty');
        if (empty) empty.style.display = tray.querySelector('.fp-chip') ? 'none' : '';
    }

    // ---- Floor tabs: show one grid at a time ----
    if (tabs) {
        tabs.addEventListener('click', e => {
            const tab = e.target.closest('.fp-tab');
            if (!tab || e.target.closest('.fp-tab-del-form')) return; // let the delete form submit
            const floor = tab.dataset.floor;
            tabs.querySelectorAll('.fp-tab').forEach(t => t.classList.toggle('active', t === tab));
            grids.forEach(g => { g.style.display = g.dataset.floor === floor ? '' : 'none'; });
        });
    }

    // ---- Drag & drop ----
    document.addEventListener('dragstart', e => {
        const chip = e.target.closest('.fp-chip');
        if (!chip) return;
        dragged = chip;
        chip.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', chip.dataset.id);
    });
    document.addEventListener('dragend', () => {
        if (dragged) dragged.classList.remove('dragging');
        dragged = null;
        document.querySelectorAll('.drag-over').forEach(el => el.classList.remove('drag-over'));
    });

    function allowDrop(el) {
        el.addEventListener('dragover', e => { e.preventDefault(); el.classList.add('drag-over'); });
        el.addEventListener('dragleave', () => el.classList.remove('drag-over'));
    }

    grids.forEach(grid => {
        const floor = grid.dataset.floor;
        grid.querySelectorAll('.fp-cell').forEach(cell => {
            allowDrop(cell);
            cell.addEventListener('drop', e => {
                e.preventDefault();
                cell.classList.remove('drag-over');
                if (!dragged) return;
                const occupant = cell.querySelector('.fp-chip');
                if (occupant && occupant !== dragged) return; // cell taken — reject
                cell.appendChild(dragged);
                dragged.dataset.x = cell.dataset.x;
                dragged.dataset.y = cell.dataset.y;
                dragged.dataset.floor = floor;
                syncTrayEmpty();
                markDirty();
            });
        });
    });

    allowDrop(tray);
    tray.addEventListener('drop', e => {
        e.preventDefault();
        tray.classList.remove('drag-over');
        if (!dragged) return;
        tray.appendChild(dragged);
        delete dragged.dataset.x;
        delete dragged.dataset.y;
        delete dragged.dataset.floor;
        syncTrayEmpty();
        markDirty();
    });

    saveBtn.addEventListener('click', () => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = SAVE_URL;
        const add = (n, v) => { const i = document.createElement('input'); i.type = 'hidden'; i.name = n; i.value = v; form.appendChild(i); };
        add('_token', CSRF);
        add('_method', 'PATCH');
        let idx = 0;
        // Collect every chip once (tray + all grids).
        document.querySelectorAll('#fpTray .fp-chip, .fp-grid .fp-chip').forEach(chip => {
            add(`positions[${idx}][id]`, chip.dataset.id);
            add(`positions[${idx}][pos_x]`, chip.dataset.x ?? '');
            add(`positions[${idx}][pos_y]`, chip.dataset.y ?? '');
            add(`positions[${idx}][floor_id]`, chip.dataset.floor ?? '');
            idx++;
        });
        document.body.appendChild(form);
        form.submit();
    });
})();
</script>
@endpush
@push('styles')
<style>
    .fp-section{background:var(--surface);border:1px solid var(--border);padding:18px;margin-bottom:28px;}
    .fp-bar{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;margin-bottom:16px;}
    .fp-title{font-size:1.1rem;color:var(--cream);margin:0;}
    .fp-sub{color:var(--muted);font-size:.82rem;margin:4px 0 0;}
    .fp-actions{display:flex;align-items:center;gap:12px;}
    .fp-dirty{display:none;color:#c9a84c;font-size:.8rem;}
    .fp-dirty.show{display:inline;}
    .fp-tabs{display:flex;gap:6px;flex-wrap:wrap;align-items:center;margin-bottom:16px;border-bottom:1px solid var(--border);padding-bottom:12px;}
    .fp-tab{display:flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid var(--border);border-radius:4px;cursor:pointer;background:rgba(255,255,255,.02);}
    .fp-tab.active{background:var(--surface);border-color:#c9a84c;}
    .fp-tab-name{font-size:.85rem;color:var(--cream);font-weight:600;}
    .fp-tab.active .fp-tab-name{color:#c9a84c;}
    .fp-tab-del-form{display:inline;margin:0;}
    .fp-tab-del{background:none;border:none;color:var(--muted);cursor:pointer;font-size:.7rem;padding:2px;line-height:1;}
    .fp-tab-del:hover{color:#c0392b;}
    .fp-tab-add{display:flex;align-items:center;gap:6px;padding:6px 12px;border:1px dashed var(--border);border-radius:4px;cursor:pointer;background:none;color:var(--muted);font-size:.82rem;font-family:inherit;}
    .fp-tab-add:hover{border-color:#c9a84c;color:#c9a84c;}
    .fp-no-floor{color:var(--muted);font-size:.85rem;font-style:italic;padding:30px;text-align:center;}
    .fp-stage{display:flex;gap:16px;flex-wrap:wrap;align-items:flex-start;}
    .fp-tray{flex:0 0 180px;border:1px dashed var(--border);padding:10px;min-height:120px;}
    .fp-tray.drag-over,.fp-cell.drag-over{outline:2px dashed #c9a84c;outline-offset:-2px;}
    .fp-tray-title{font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);margin-bottom:8px;}
    .fp-tray-empty{color:var(--muted);font-size:.8rem;font-style:italic;}
    .fp-grid-scroll{flex:1 1 480px;overflow-x:auto;}
    .fp-grid{display:grid;grid-template-columns:repeat(12,minmax(54px,1fr));gap:4px;min-width:660px;}
    .fp-cell{aspect-ratio:1;border:1px solid var(--border);border-radius:3px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.02);}
    .fp-chip{background:var(--surface);border:1px solid var(--border);border-left:3px solid #27ae60;padding:6px 4px;width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:grab;text-align:center;border-radius:3px;}
    .fp-chip:active{cursor:grabbing;}
    .fp-chip.dragging{opacity:.4;}
    .fp-chip-name{font-size:.72rem;color:var(--cream);font-weight:600;line-height:1.1;word-break:break-word;}
    .fp-chip-cap{font-size:.65rem;color:var(--muted);margin-top:2px;}
    .fp-tray .fp-chip{height:auto;margin-bottom:8px;flex-direction:row;gap:6px;justify-content:flex-start;padding:8px;}
</style>
@endpush
