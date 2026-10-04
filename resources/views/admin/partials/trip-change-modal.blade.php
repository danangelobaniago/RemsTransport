{{--
    "Change Van / Driver / Route" modal (Terms & Conditions §6).
    Open with openTripChange(this) on a button carrying data-trip='{...}':
      type          rental | tour | joiner
      id            booking / tour package / joiner trip id
      label         heading text, e.g. "Van Rental #12"
      van_plate     current van's plate number
      driver_name   current driver's name
      pickup        current pickup / meetup point
      destination   current destination
      seats         seats the replacement van must have
--}}
@php
    $tcVans = \Illuminate\Support\Facades\DB::table('vans')->orderBy('name')->get(['id', 'name', 'plate_number', 'seats', 'status']);
    $tcDrivers = \Illuminate\Support\Facades\DB::table('drivers')->orderBy('name')->get(['id', 'name', 'status']);
@endphp

<style>
    .tc-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, .55); z-index: 3000; display: flex; align-items: center; justify-content: center; padding: 16px; }
    .tc-overlay[hidden] { display: none; }
    .tc-modal { background: #fff; border-radius: 14px; width: 100%; max-width: 560px; max-height: calc(100vh - 32px); overflow-y: auto; box-shadow: 0 20px 50px rgba(0,0,0,.3); font-family: 'Poppins', sans-serif; }
    .tc-head { padding: 18px 22px 12px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: flex-start; gap: 12px; }
    .tc-head h3 { margin: 0; font-size: 17px; color: #111827; }
    .tc-head p { margin: 4px 0 0; font-size: 12px; color: #6b7280; line-height: 1.45; }
    .tc-x { margin-left: auto; background: none; border: none; font-size: 24px; line-height: 1; color: #6b7280; cursor: pointer; }
    .tc-body { padding: 16px 22px; display: grid; gap: 12px; }
    .tc-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .tc-field label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 5px; }
    .tc-field select, .tc-field input, .tc-field textarea {
        width: 100%; padding: 9px 11px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; font-family: inherit; background: #fff; color: #111827;
    }
    .tc-field select:focus, .tc-field input:focus, .tc-field textarea:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
    .tc-field small { display: block; font-size: 11px; color: #6b7280; margin-top: 4px; }
    .tc-current { font-size: 12px; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px 12px; color: #374151; line-height: 1.6; }
    .tc-foot { padding: 12px 22px 18px; display: flex; justify-content: flex-end; gap: 8px; }
    .tc-btn { padding: 9px 16px; border: none; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer; }
    .tc-btn.cancel { background: #e5e7eb; color: #374151; }
    .tc-btn.save { background: #2563eb; color: #fff; }
    .tc-history { border-top: 1px solid #e5e7eb; padding: 12px 22px 4px; }
    .tc-history h4 { margin: 0 0 8px; font-size: 13px; color: #111827; }
    .tc-hist-item { font-size: 12px; color: #374151; border-left: 3px solid #93c5fd; padding: 4px 0 4px 10px; margin-bottom: 10px; line-height: 1.5; }
    .tc-hist-item .when { color: #6b7280; font-size: 11px; }
    .icon-btn-change { background: #e0f2fe; color: #0369a1; }
    .icon-btn-change:hover { background: #bae6fd; }
    @media (max-width: 520px) { .tc-row { grid-template-columns: 1fr; } }
</style>

<div class="tc-overlay" id="tcOverlay" hidden>
    <form class="tc-modal" id="tcForm" method="POST" action="">
        @csrf
        <div class="tc-head">
            <div>
                <h3><i class="fas fa-shuffle" style="color:#2563eb;"></i> Change Van / Driver / Route</h3>
                <p id="tcLabel"></p>
            </div>
            <button type="button" class="tc-x" onclick="closeTripChange()" aria-label="Close">&times;</button>
        </div>

        <div class="tc-body">
            <div class="tc-current" id="tcCurrent"></div>

            <div class="tc-row">
                <div class="tc-field">
                    <label for="tcVan">Van</label>
                    <select name="van_id" id="tcVan">
                        @foreach($tcVans as $v)
                            <option value="{{ $v->id }}" data-plate="{{ $v->plate_number }}" data-seats="{{ $v->seats }}" data-status="{{ $v->status }}">
                                {{ $v->name }} ({{ $v->plate_number }}) · {{ $v->seats }} seats{{ $v->status !== 'available' ? ' · unavailable' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <small id="tcSeatsHint"></small>
                </div>
                <div class="tc-field">
                    <label for="tcDriver">Driver</label>
                    <select name="driver_id" id="tcDriver">
                        @foreach($tcDrivers as $d)
                            <option value="{{ $d->id }}" data-name="{{ $d->name }}" data-status="{{ $d->status }}">
                                {{ $d->name }}{{ $d->status !== 'available' ? ' · unavailable' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="tc-row">
                <div class="tc-field">
                    <label for="tcPickup" id="tcPickupLabel">Pickup point</label>
                    <input type="text" name="pickup" id="tcPickup" maxlength="255">
                </div>
                <div class="tc-field">
                    <label for="tcDestination">Destination</label>
                    <input type="text" name="destination" id="tcDestination" maxlength="255">
                </div>
            </div>

            <div class="tc-field">
                <label for="tcReason">Reason for the change</label>
                <select name="reason" id="tcReason" required>
                    <option value="" disabled selected>Select a reason...</option>
                    @foreach(\App\Http\Controllers\TripChangeController::REASONS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="tc-field">
                <label for="tcDetails">Details (sent to the customer)</label>
                <textarea name="details" id="tcDetails" rows="3" maxlength="500" required placeholder="e.g. The original van failed its pre-trip inspection (brake issue). A same-size van has been assigned."></textarea>
            </div>
        </div>

        <div class="tc-history" id="tcHistory" hidden>
            <h4><i class="fas fa-clock-rotate-left"></i> Change history</h4>
            <div id="tcHistoryList"></div>
        </div>

        <div class="tc-foot">
            <button type="button" class="tc-btn cancel" onclick="closeTripChange()">Cancel</button>
            <button type="submit" class="tc-btn save" id="tcSave">Save &amp; Notify</button>
        </div>
    </form>
</div>

<script>
(function () {
    const overlay = document.getElementById('tcOverlay');
    const form = document.getElementById('tcForm');
    const vanSel = document.getElementById('tcVan');
    const drvSel = document.getElementById('tcDriver');
    const seatsHint = document.getElementById('tcSeatsHint');
    let trip = null;

    function pick(select, attr, value) {
        let found = false;
        Array.from(select.options).forEach(o => {
            const match = value && o.dataset[attr] === String(value);
            o.selected = match;
            found = found || match;
        });
        if (!found) {
            // Current van/driver isn't in the list (e.g. typed in manually) — show a placeholder.
            let ph = select.querySelector('option[data-placeholder]');
            if (!ph) {
                ph = document.createElement('option');
                ph.dataset.placeholder = '1';
                ph.value = '';
                select.prepend(ph);
            }
            ph.textContent = value ? value + ' (current)' : '— None assigned —';
            ph.selected = true;
        }
    }

    function refreshSeats() {
        const o = vanSel.selectedOptions[0];
        const need = trip ? trip.seats : 0;
        if (!o || !o.dataset.seats || !need) { seatsHint.textContent = ''; return; }
        const ok = Number(o.dataset.seats) >= need;
        seatsHint.style.color = ok ? '#6b7280' : '#dc2626';
        seatsHint.textContent = ok
            ? `Trip needs ${need} seat(s).`
            : `Too small: this trip needs ${need} seat(s).`;
    }

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s ?? '';
        return d.innerHTML;
    }

    window.openTripChange = function (btn) {
        trip = JSON.parse(btn.dataset.trip);
        form.action = `/admin/trip-change/${trip.type}/${trip.id}`;
        document.getElementById('tcLabel').textContent = trip.label;
        document.getElementById('tcPickupLabel').textContent = trip.type === 'joiner' ? 'Meetup point' : 'Pickup point';
        document.getElementById('tcCurrent').innerHTML =
            `<strong>Current:</strong> ${esc(trip.van_label || 'No van')} · ${esc(trip.driver_name || 'No driver')}<br>` +
            `<strong>Route:</strong> ${esc(trip.pickup || 'N/A')} → ${esc(trip.destination || 'N/A')}`;

        pick(vanSel, 'plate', trip.van_plate);
        pick(drvSel, 'name', trip.driver_name);
        document.getElementById('tcPickup').value = trip.pickup || '';
        document.getElementById('tcDestination').value = trip.destination || '';
        document.getElementById('tcReason').selectedIndex = 0;
        document.getElementById('tcDetails').value = '';
        refreshSeats();

        const hist = document.getElementById('tcHistory');
        const list = document.getElementById('tcHistoryList');
        hist.hidden = true;
        list.innerHTML = '';
        fetch(`/admin/trip-change/${trip.type}/${trip.id}/history`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(r => r.ok ? r.json() : { history: [] })
            .then(d => {
                if (!d.history.length) return;
                list.innerHTML = d.history.map(h =>
                    `<div class="tc-hist-item"><div class="when">${esc(h.when)} · by ${esc(h.by)}</div>` +
                    h.changes.map(c => esc(c)).join('<br>') +
                    `<br><em>${esc(h.reason)}</em></div>`).join('');
                hist.hidden = false;
            })
            .catch(() => {});

        overlay.hidden = false;
    };

    window.closeTripChange = function () { overlay.hidden = true; };

    vanSel.addEventListener('change', refreshSeats);
    overlay.addEventListener('click', e => { if (e.target === overlay) closeTripChange(); });

    form.addEventListener('submit', function (e) {
        const o = vanSel.selectedOptions[0];
        if (o && o.dataset.seats && trip.seats && Number(o.dataset.seats) < trip.seats) {
            e.preventDefault();
            alert('The selected van does not have enough seats for this trip. Choose a comparable van.');
            return;
        }
        document.getElementById('tcSave').disabled = true;
        document.getElementById('tcSave').textContent = 'Saving...';
    });
})();
</script>
