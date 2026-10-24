{{--
    "Edit Passenger" modal for replacing someone on a manifest (tour and joiner pages).
    Open with openPassengerEditor({ url, passenger, withContact, onSaved }):
      url          POST endpoint (/admin/passengers/{id} or /admin/joiner-passengers/{id})
      passenger    { first_name, middle_name, last_name, suffix, birthday, gender, contact }
      withContact  show the contact number field (joiner passengers have one)
      onSaved      called after a successful save
--}}
<style>
    .pe-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, .55); z-index: 4000; display: flex; align-items: center; justify-content: center; padding: 16px; }
    .pe-overlay[hidden] { display: none; }
    .pe-modal { background: #fff; border-radius: 14px; width: 100%; max-width: 520px; max-height: calc(100vh - 32px); overflow-y: auto; box-shadow: 0 20px 50px rgba(0,0,0,.3); font-family: inherit; }
    .pe-head { padding: 18px 22px 12px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: flex-start; gap: 12px; }
    .pe-head h3 { margin: 0; font-size: 17px; color: #111827; }
    .pe-head p { margin: 4px 0 0; font-size: 12px; color: #6b7280; line-height: 1.45; }
    .pe-x { margin-left: auto; background: none; border: none; font-size: 24px; line-height: 1; color: #6b7280; cursor: pointer; }
    .pe-body { padding: 16px 22px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .pe-body label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 5px; }
    .pe-body input, .pe-body select { width: 100%; padding: 9px 11px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; font-family: inherit; box-sizing: border-box; }
    .pe-error { grid-column: 1 / -1; background: #fee2e2; color: #991b1b; border-radius: 8px; padding: 8px 12px; font-size: 13px; }
    .pe-foot { padding: 12px 22px 18px; display: flex; justify-content: flex-end; gap: 8px; }
    .pe-btn { padding: 9px 16px; border: none; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer; }
    .pe-edit-link { background: #eff6ff; color: #2563eb; border: none; border-radius: 6px; padding: 2px 8px; font-size: 11px; font-weight: 600; cursor: pointer; margin-left: 6px; }
    @media (max-width: 520px) { .pe-body { grid-template-columns: 1fr; } }
    @media print { .pe-edit-link, .pe-overlay { display: none !important; } }
</style>

<div class="pe-overlay" id="peOverlay" hidden>
    <form class="pe-modal" id="peForm">
        <div class="pe-head">
            <div>
                <h3><i class="fas fa-user-pen" style="color:#2563eb;"></i> Edit Passenger</h3>
                <p>Replace a passenger who can no longer join. The number of passengers stays the same, and the customer is notified.</p>
            </div>
            <button type="button" class="pe-x" onclick="closePassengerEditor()" aria-label="Close">&times;</button>
        </div>
        <div class="pe-body">
            <div><label>First name</label><input name="first_name" maxlength="30" required></div>
            <div><label>Middle name</label><input name="middle_name" maxlength="30"></div>
            <div><label>Last name</label><input name="last_name" maxlength="30" required></div>
            <div><label>Suffix</label>
                <select name="suffix">
                    @foreach(['' => '—', 'Jr.' => 'Jr.', 'Sr.' => 'Sr.', 'II' => 'II', 'III' => 'III', 'IV' => 'IV', 'V' => 'V'] as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>Birthday</label><input type="date" name="birthday" required max="{{ now()->subMonth()->toDateString() }}"></div>
            <div><label>Gender</label>
                <select name="gender" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div id="peContactField"><label>Contact number</label><input name="contact" maxlength="11" inputmode="numeric" placeholder="09XXXXXXXXX"></div>
            <div class="pe-error" id="peError" hidden></div>
        </div>
        <div class="pe-foot">
            <button type="button" class="pe-btn" style="background:#e5e7eb;color:#374151;" onclick="closePassengerEditor()">Cancel</button>
            <button type="submit" class="pe-btn" id="peSave" style="background:#2563eb;color:#fff;">Save</button>
        </div>
    </form>
</div>

<script>
(function () {
    const overlay = document.getElementById('peOverlay');
    const form = document.getElementById('peForm');
    const errorBox = document.getElementById('peError');
    const saveBtn = document.getElementById('peSave');
    let current = null;

    window.openPassengerEditor = function (opts) {
        current = opts;
        const p = opts.passenger || {};
        ['first_name', 'middle_name', 'last_name', 'suffix', 'birthday', 'gender', 'contact'].forEach(f => {
            if (form.elements[f]) form.elements[f].value = p[f] ?? '';
        });
        if (!form.elements.gender.value) form.elements.gender.value = 'Male';
        document.getElementById('peContactField').hidden = !opts.withContact;
        form.elements.contact.required = !!opts.withContact;
        errorBox.hidden = true;
        saveBtn.disabled = false; saveBtn.textContent = 'Save';
        overlay.hidden = false;
    };

    window.closePassengerEditor = function () { overlay.hidden = true; };
    overlay.addEventListener('click', e => { if (e.target === overlay) closePassengerEditor(); });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        saveBtn.disabled = true; saveBtn.textContent = 'Saving...';
        const body = new FormData(form);
        if (!current.withContact) body.delete('contact');

        fetch(current.url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body,
        })
            .then(r => r.json().then(d => ({ ok: r.ok, d })))
            .then(({ ok, d }) => {
                if (!ok) throw new Error(d.error || 'Could not save the passenger.');
                closePassengerEditor();
                if (current.onSaved) current.onSaved(d);
            })
            .catch(err => {
                errorBox.textContent = err.message;
                errorBox.hidden = false;
                saveBtn.disabled = false; saveBtn.textContent = 'Save';
            });
    });
})();
</script>
