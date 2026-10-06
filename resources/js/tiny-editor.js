/**
 * Alpine component behind App\Filament\Forms\Components\TinyEditor:
 * loads TinyMCE Cloud once per page, keeps the editor and the Livewire
 * state in sync, follows the panel's dark mode and uploads pasted or
 * inserted images to the staff-only editor upload route.
 *
 * Livewire may re-render the form (e.g. when a page opens on a given tab)
 * while TinyMCE is still loading, which detaches the textarea an earlier
 * Alpine instance holds. An editor is therefore only started on a textarea
 * that is still in the page, each instance gets its own element id, and an
 * instance whose textarea left the page cleans itself up.
 */
(function () {
    let loading = null;
    let counter = 0;

    function loadTiny(src) {
        if (window.tinymce) return Promise.resolve();
        if (!loading) {
            loading = new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = src;
                script.referrerPolicy = 'origin';
                script.onload = resolve;
                script.onerror = () => { loading = null; reject(new Error('TinyMCE tidak dapat dimuat')); };
                document.head.appendChild(script);
            });
        }
        return loading;
    }

    function uploadHandler(url) {
        return (blobInfo, progress) => new Promise((resolve, reject) => {
            const data = new FormData();
            data.append('file', blobInfo.blob(), blobInfo.filename());
            const xhr = new XMLHttpRequest();
            xhr.open('POST', url);
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]')?.content ?? '');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.upload.onprogress = (e) => progress((e.loaded / e.total) * 100);
            xhr.onload = () => {
                let json = {};
                try { json = JSON.parse(xhr.responseText); } catch (e) { /* not JSON */ }
                if (xhr.status >= 200 && xhr.status < 300 && json.location) {
                    resolve(json.location);
                } else {
                    reject({ message: json.message ?? 'Gambar gagal diunggah (HTTP ' + xhr.status + ').', remove: true });
                }
            };
            xhr.onerror = () => reject({ message: 'Gambar gagal diunggah.', remove: true });
            xhr.send(data);
        });
    }

    window.ptspTinyEditor = ({ state, config }) => ({
        state,
        editor: null,
        booting: false,

        init() {
            if (!config.script) return;

            const textarea = this.$refs.textarea;
            textarea.id = (textarea.id || 'tiny-editor') + '-' + (++counter);

            loadTiny(config.script)
                .then(() => this.$nextTick(() => this.boot()))
                .catch((error) => console.error(error));
        },

        boot() {
            const textarea = this.$refs.textarea;
            if (this.editor || this.booting || !textarea || !textarea.isConnected) return;
            this.booting = true;

            window.tinymce.get(textarea.id)?.remove();

            const dark = document.documentElement.classList.contains('dark');
            const sync = () => {
                if (!this.editor) return;
                const html = this.editor.getContent();
                if (html !== (this.state ?? '')) this.state = html;
            };

            window.tinymce.init({
                ...config.options,
                target: textarea,
                skin: dark ? 'oxide-dark' : 'oxide',
                content_css: dark ? 'dark' : 'default',
                readonly: config.disabled,
                images_upload_handler: config.uploadUrl ? uploadHandler(config.uploadUrl) : undefined,
                automatic_uploads: Boolean(config.uploadUrl),
                setup: (editor) => {
                    editor.on('init', () => {
                        this.editor = editor;
                        this.booting = false;
                        editor.setContent(this.state ?? '');
                    });
                    editor.on('change input undo redo ExecCommand', () => sync());
                    editor.on('blur', () => sync());
                },
            }).catch((error) => {
                this.booting = false;
                console.error(error);
            });

            this.$watch('state', (value) => {
                if (this.editor?.initialized && (value ?? '') !== this.editor.getContent()) {
                    this.editor.setContent(value ?? '');
                }
            });
        },

        destroy() {
            this.editor?.remove();
            this.editor = null;
        },
    });
})();
