{{--
    Survey draft: answers are kept in this browser so an interrupted survey can
    be resumed. Survey kiosks are shared, so only choices (radio/select/checkbox)
    and suggestion boxes are stored, never typed identity fields (name, phone,
    e-mail, numbers); drafts expire after 24 hours and are cleared once sent.
    $form: the form's id; omit it to only clear the draft (success page).
--}}
<script>
(() => {
    const KEY = 'ptsp-survey-draft';
    const TTL = 24 * 60 * 60 * 1000;
    const read = () => {
        try {
            const draft = JSON.parse(localStorage.getItem(KEY) || 'null');
            return draft && Date.now() - draft.savedAt < TTL ? draft : { savedAt: Date.now(), forms: {} };
        } catch (e) {
            return { savedAt: Date.now(), forms: {} };
        }
    };
    const write = (draft) => {
        try { localStorage.setItem(KEY, JSON.stringify({ ...draft, savedAt: Date.now() })); } catch (e) {}
    };

    @if (empty($form))
        try { localStorage.removeItem(KEY); } catch (e) {}
    @else
        const form = document.getElementById(@json($form));
        if (! form) return;
        const kept = (el) => el.name && ! el.disabled && (
            el.type === 'radio' || el.type === 'checkbox' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA'
        );
        const fields = () => [...form.elements].filter(kept);

        // Restore only where the server did not fill anything in (e.g. after a validation error).
        const saved = read().forms[@json($form)] || {};
        let restored = 0;
        fields().forEach((el) => {
            if (! (el.name in saved)) return;
            const value = saved[el.name];
            if (el.type === 'radio') {
                const group = form.querySelectorAll(`[name="${CSS.escape(el.name)}"]`);
                if (! [...group].some((r) => r.checked) && el.value === value) { el.checked = true; restored++; }
            } else if (el.type === 'checkbox') {
                if (Array.isArray(value) && value.includes(el.value) && ! el.checked) { el.checked = true; restored++; }
            } else if (! el.value && value) {
                el.value = value; restored++;
            }
        });

        if (restored) {
            const note = document.createElement('div');
            note.className = 'alert alert-info py-2 small';
            note.setAttribute('role', 'status');
            note.textContent = 'Jawaban yang belum terkirim dipulihkan dari perangkat ini.';
            form.prepend(note);
        }

        const save = () => {
            const values = {};
            fields().forEach((el) => {
                if (el.type === 'radio') { if (el.checked) values[el.name] = el.value; }
                else if (el.type === 'checkbox') { if (el.checked) (values[el.name] ||= []).push(el.value); }
                else if (el.value) values[el.name] = el.value;
            });
            const draft = read();
            draft.forms[@json($form)] = values;
            write(draft);
        };
        form.addEventListener('change', save);
        form.addEventListener('input', save);
    @endif
})();
</script>
