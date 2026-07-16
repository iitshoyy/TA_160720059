<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Reservation — RestoPOS</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;background:#f5f2ec;color:#2c2416;min-height:100vh;font-size:14px}
        .hero{background:#1a1814;color:#fff;padding:32px 20px;text-align:center}
        .hero-title{font-size:1.4rem;color:#c9a84c;margin-bottom:4px;font-weight:700}
        .hero-sub{color:#8a7d6b;font-size:.85rem}
        .container{max-width:560px;margin:0 auto;padding:24px 16px}
        .card{background:#fff;border:1px solid #e8e0d0;padding:24px}
        .form-group{margin-bottom:14px}
        label{display:block;font-size:.82rem;color:#6b6455;margin-bottom:4px}
        input,select,textarea{width:100%;padding:8px 12px;border:1px solid #e8e0d0;font-size:.9rem;font-family:inherit;color:#2c2416;background:#fff}
        input:focus,select:focus,textarea:focus{outline:none;border-color:#c9a84c}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .btn{width:100%;padding:10px;background:#c9a84c;color:#000;border:1px solid #c9a84c;font-size:.95rem;font-weight:600;cursor:pointer;font-family:inherit}
        .btn:hover{background:#b8960a;border-color:#b8960a}
        .btn:disabled{background:#d8cfb8;border-color:#d8cfb8;color:#8a7d6b;cursor:not-allowed}
        .success{color:#155724;border:1px solid #155724;padding:10px 14px;margin-bottom:16px;font-size:.875rem}
        .errors{color:#721c24;border:1px solid #721c24;padding:10px 14px;margin-bottom:16px;font-size:.875rem}
        .section-title{font-size:1.05rem;color:#2c2416;margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid #e8e0d0;font-weight:600}
        .info-box{border:1px solid #e8e0d0;padding:12px;margin-bottom:16px;font-size:.82rem;color:#6b6455;line-height:1.6}
        .slots{display:grid;grid-template-columns:repeat(4,1fr);gap:8px}
        .slot{padding:8px 4px;border:1px solid #e8e0d0;background:#fff;text-align:center;cursor:pointer;font-size:.85rem;font-family:inherit}
        .slot.active{background:#1a1814;color:#c9a84c;border-color:#1a1814}
        .tables{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
        .table-card{padding:12px 4px;border:1px solid #e8e0d0;background:#fff;text-align:center;cursor:pointer}
        .table-card .tname{font-weight:600}
        .table-card .tcap{font-size:.78rem;color:#6b6455}
        .table-card.active{background:#1a1814;border-color:#1a1814}
        .table-card.active .tname,.table-card.active .tcap{color:#c9a84c}
        .table-card.disabled{opacity:.4;cursor:not-allowed;background:#f0ebe0}
        .map-legend{font-size:.78rem;color:#8a7d6b;margin-bottom:8px}
        .floor-pills{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px}
        .floor-pill{padding:6px 14px;border:1px solid #e8e0d0;background:#fff;font-size:.82rem;font-family:inherit;cursor:pointer;color:#6b6455}
        .floor-pill.active{background:#1a1814;color:#c9a84c;border-color:#1a1814}
        .res-map-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
        .res-map{display:grid;grid-template-columns:repeat(12,minmax(46px,1fr));grid-auto-rows:minmax(46px,auto);gap:6px;min-width:560px;padding:12px;background:#f0ebe0;border:1px solid #e8e0d0}
        .map-card{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:6px 2px}
        .map-card .tname{font-size:.78rem;line-height:1.1}
        .map-card .tcap{font-size:.68rem}
        .res-list-label{font-size:.82rem;color:#6b6455;margin:12px 0 6px}
        .hint{font-size:.8rem;color:#8a7d6b;margin-top:8px}
        @media(max-width:480px){.form-row{grid-template-columns:1fr}.slots{grid-template-columns:repeat(3,1fr)}}
    </style>
</head>
<body>
<div class="hero">
    <div class="hero-title">🍽️ RestoPOS</div>
    <div class="hero-sub">Online Table Reservation</div>
</div>
<div class="container">
    @if(session('success'))
    <div class="success">✅ {{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="errors">⚠️ {{ $errors->first() }}</div>
    @endif

    <div class="card">
        <div class="section-title">Book Your Table</div>
        <div class="info-box">
            📞 We'll confirm your reservation via phone/WhatsApp within 30 minutes.<br>
            ⏰ Reservations available daily from 10:00 – 20:00.<br>
            👥 For groups of 10+, please call us directly.
        </div>
        <form method="POST" action="{{ route('reservation.public.store') }}" id="resForm">
        @csrf
            <div class="form-row">
                <div class="form-group"><label>Full Name *</label><input name="customer_name" required placeholder="Your full name" value="{{ old('customer_name') }}"></div>
                <div class="form-group"><label>Phone Number *</label><input name="phone" required placeholder="08xx-xxxx-xxxx" value="{{ old('phone') }}"></div>
            </div>
            <div class="form-group"><label>Email</label><input type="email" name="email" placeholder="Optional" value="{{ old('email') }}"></div>
            <div class="form-row">
                <div class="form-group"><label>Date *</label><input type="date" id="dateInput" name="reservation_date" required min="{{ date('Y-m-d') }}" value="{{ old('reservation_date', date('Y-m-d')) }}"></div>
                <div class="form-group"><label>Number of Guests *</label>
                    <select name="guests" id="guestsInput" required>
                        @for($i=1;$i<=12;$i++)<option value="{{ $i }}" {{ old('guests',2)==$i?'selected':'' }}>{{ $i }} {{ $i==1?'person':'people' }}</option>@endfor
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Time Slot *</label>
                <div class="slots" id="slotGrid">
                    @foreach($slots as $slot)
                    <div class="slot" data-slot="{{ $slot }}">{{ $slot }}</div>
                    @endforeach
                </div>
                <input type="hidden" name="reservation_time" id="timeInput" value="{{ old('reservation_time') }}">
            </div>

            <div class="form-group">
                <label>Choose a Table *</label>
                @php
                    $isPlaced = fn ($t) => ! is_null($t->floor_id) && ! is_null($t->pos_x) && ! is_null($t->pos_y);
                    $placedByFloor = $tables->filter($isPlaced)->groupBy('floor_id');
                    $unplaced = $tables->filter(fn ($t) => ! $isPlaced($t));
                    // Only floors that actually have placed tables, in the given order.
                    $mapFloors = $floors->filter(fn ($f) => $placedByFloor->has($f->id))->values();
                @endphp
                <div id="tableGrid">
                    @if($mapFloors->count())
                    <div class="map-legend">🟢 tap an available table · ⬜ unavailable</div>

                    @if($mapFloors->count() > 1)
                    <div class="floor-pills" id="floorPills">
                        @foreach($mapFloors as $i => $floor)
                        <button type="button" class="floor-pill {{ $i === 0 ? 'active' : '' }}" data-floor="{{ $floor->id }}">{{ $floor->name }}</button>
                        @endforeach
                    </div>
                    @endif

                    @foreach($mapFloors as $i => $floor)
                    <div class="res-map-wrap floor-map" data-floor="{{ $floor->id }}" style="{{ $i === 0 ? '' : 'display:none' }}">
                        <div class="res-map">
                            @foreach($placedByFloor->get($floor->id) as $t)
                            <div class="table-card map-card disabled" data-table-id="{{ $t->id }}" data-capacity="{{ $t->capacity }}" style="grid-column:{{ $t->pos_x + 1 }};grid-row:{{ $t->pos_y + 1 }};">
                                <div class="tname">{{ $t->name }}</div>
                                <div class="tcap">👥 {{ $t->capacity }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                    @endif

                    @if($unplaced->count())
                    @if($mapFloors->count())<div class="res-list-label">Other tables</div>@endif
                    <div class="tables">
                        @foreach($unplaced as $t)
                        <div class="table-card disabled" data-table-id="{{ $t->id }}" data-capacity="{{ $t->capacity }}">
                            <div class="tname">{{ $t->name }}</div>
                            <div class="tcap">👥 {{ $t->capacity }}</div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                <input type="hidden" name="table_id" id="tableInput" value="{{ old('table_id') }}">
                <div class="hint" id="tableHint">Pick a date and time slot to see available tables.</div>
            </div>

            <div class="form-group"><label>Special Notes / Requests</label><textarea name="notes" rows="3" placeholder="Birthday celebration, dietary restrictions, special seating requests...">{{ old('notes') }}</textarea></div>
            <button type="submit" class="btn" id="submitBtn" disabled>📅 Submit Reservation</button>
        </form>
    </div>
</div>

<script>
(function () {
    const availabilityUrl = "{{ route('reservation.availability') }}";
    const dateInput  = document.getElementById('dateInput');
    const guestsInput= document.getElementById('guestsInput');
    const timeInput  = document.getElementById('timeInput');
    const tableInput = document.getElementById('tableInput');
    const slotGrid   = document.getElementById('slotGrid');
    const tableGrid  = document.getElementById('tableGrid');
    const tableHint  = document.getElementById('tableHint');
    const submitBtn  = document.getElementById('submitBtn');

    function syncSubmit() {
        submitBtn.disabled = !(timeInput.value && tableInput.value);
    }

    function clearTableSelection() {
        tableInput.value = '';
        tableGrid.querySelectorAll('.table-card').forEach(c => c.classList.remove('active'));
        syncSubmit();
    }

    async function refreshTables() {
        const date = dateInput.value, time = timeInput.value, guests = guestsInput.value;
        const cards = tableGrid.querySelectorAll('.table-card');

        if (!date || !time) {
            cards.forEach(c => c.classList.add('disabled'));
            tableHint.textContent = 'Pick a date and time slot to see available tables.';
            clearTableSelection();
            return;
        }

        tableHint.textContent = 'Checking availability…';
        let available = [];
        try {
            const url = `${availabilityUrl}?date=${encodeURIComponent(date)}&time=${encodeURIComponent(time)}&guests=${encodeURIComponent(guests)}`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (res.ok) { available = (await res.json()).available || []; }
        } catch (e) { /* network error → treat as none available */ }

        let freeCount = 0;
        cards.forEach(c => {
            const id = parseInt(c.dataset.tableId, 10);
            const ok = available.includes(id);
            c.classList.toggle('disabled', !ok);
            if (ok) freeCount++;
            if (!ok && tableInput.value === String(id)) { clearTableSelection(); }
        });

        tableHint.textContent = freeCount
            ? `${freeCount} table(s) available — tap one to select.`
            : 'No tables available for this date and time. Try another slot.';
    }

    slotGrid.addEventListener('click', e => {
        const slot = e.target.closest('.slot');
        if (!slot) return;
        slotGrid.querySelectorAll('.slot').forEach(s => s.classList.remove('active'));
        slot.classList.add('active');
        timeInput.value = slot.dataset.slot;
        clearTableSelection();
        refreshTables();
    });

    tableGrid.addEventListener('click', e => {
        const card = e.target.closest('.table-card');
        if (!card || card.classList.contains('disabled')) return;
        tableGrid.querySelectorAll('.table-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        tableInput.value = card.dataset.tableId;
        syncSubmit();
    });

    dateInput.addEventListener('change', refreshTables);
    guestsInput.addEventListener('change', refreshTables);

    // Floor switcher: show one floor's map at a time (availability still spans all).
    const floorPills = document.getElementById('floorPills');
    if (floorPills) {
        floorPills.addEventListener('click', e => {
            const pill = e.target.closest('.floor-pill');
            if (!pill) return;
            const floor = pill.dataset.floor;
            floorPills.querySelectorAll('.floor-pill').forEach(p => p.classList.toggle('active', p === pill));
            document.querySelectorAll('.floor-map').forEach(m => {
                m.style.display = m.dataset.floor === floor ? '' : 'none';
            });
        });
    }

    // Restore old() slot selection after a validation redirect.
    if (timeInput.value) {
        const pre = slotGrid.querySelector(`.slot[data-slot="${timeInput.value}"]`);
        if (pre) pre.classList.add('active');
        refreshTables();
    }
})();
</script>
</body>
</html>
