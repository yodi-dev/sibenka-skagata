<div x-data="universalConfirmModal()"
     x-on:open-confirm-modal.window="open($event.detail)"
     x-cloak
     class="relative z-[9999]"
     aria-labelledby="modal-title"
     role="dialog"
     aria-modal="true"
     x-show="isOpen">

    <!-- Backdrop Overlay with smooth fade & blur -->
    <div x-show="isOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
         @click="cancel()"></div>

    <!-- Modal Dialog Positioner -->
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="isOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl border border-gray-100 transition-all sm:my-8 sm:w-full sm:max-w-lg"
                 @keydown.escape.window="cancel()">

                <div class="p-6">
                    <div class="sm:flex sm:items-start gap-4">
                        <!-- Dynamic Icon Badge -->
                        <div class="mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl sm:mx-0 sm:h-12 sm:w-12 shadow-sm"
                             :class="{
                                 'bg-red-50 text-red-600 border border-red-100': type === 'danger',
                                 'bg-emerald-50 text-emerald-600 border border-emerald-100': type === 'success',
                                 'bg-amber-50 text-amber-600 border border-amber-100': type === 'warning',
                                 'bg-primary-50 text-primary-600 border border-primary-100': type === 'primary' || type === 'info'
                             }">

                            <!-- Danger Icon (Delete / Tolak / Suspend) -->
                            <template x-if="type === 'danger'">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </template>

                            <!-- Success Icon (Approve / Setujui / Terima) -->
                            <template x-if="type === 'success'">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>

                            <!-- Warning Icon (Alert / Peringatan / Cek) -->
                            <template x-if="type === 'warning'">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </template>

                            <!-- Primary / Info Icon (Create / Submit / Kirim / Update) -->
                            <template x-if="type === 'primary' || type === 'info'">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                        </div>

                        <!-- Content Area -->
                        <div class="mt-3 text-center sm:mt-0 sm:text-left flex-1 min-w-0">
                            <h3 class="text-base font-bold text-gray-900 leading-6" id="modal-title" x-text="title">
                                Konfirmasi Tindakan
                            </h3>
                            <div class="mt-2 text-sm text-gray-600 leading-relaxed" x-html="message">
                                Apakah Anda yakin ingin melanjutkan tindakan ini?
                            </div>

                            <template x-if="subMessage">
                                <p class="mt-2 text-xs text-gray-500 italic bg-gray-50 p-2.5 rounded-lg border border-gray-100" x-text="subMessage"></p>
                            </template>

                            <!-- Optional Textarea Input (e.g. Alasan Penolakan / Catatan) -->
                            <template x-if="withInput">
                                <div class="mt-4">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1" x-text="inputLabel || 'Alasan / Keterangan:'"></label>
                                    <textarea x-model="inputValue"
                                              :placeholder="inputPlaceholder || 'Tuliskan alasan atau catatan di sini...'"
                                              rows="3"
                                              class="w-full text-sm border-gray-300 rounded-xl shadow-xs focus:border-primary-500 focus:ring-primary-500"
                                              :required="inputRequired"></textarea>
                                    <p x-show="inputError" class="text-xs text-red-600 mt-1 font-medium" x-text="inputError"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons Footer -->
                <div class="bg-gray-50/80 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5 border-t border-gray-100">
                    <button type="button"
                            x-show="showCancel && !isAlert"
                            @click="cancel()"
                            class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2.5 rounded-xl border border-gray-300 bg-white text-sm font-semibold text-gray-700 shadow-xs hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors"
                            x-text="cancelText">
                        Batal
                    </button>

                    <button type="button"
                            @click="confirm()"
                            class="w-full sm:w-auto inline-flex justify-center items-center px-5 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm transition-all focus:outline-none focus:ring-2 active:scale-98"
                            :class="{
                                'bg-red-600 hover:bg-red-700 focus:ring-red-500': type === 'danger',
                                'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500': type === 'success',
                                'bg-amber-600 hover:bg-amber-700 focus:ring-amber-500': type === 'warning',
                                'bg-primary-600 hover:bg-primary-700 focus:ring-primary-500': type === 'primary' || type === 'info'
                            }">
                        <span x-text="confirmText">Ya, Lanjutkan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function universalConfirmModal() {
    return {
        isOpen: false,
        isAlert: false,
        showCancel: true,
        title: 'Konfirmasi Tindakan',
        message: 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
        subMessage: '',
        type: 'primary', // 'danger', 'success', 'warning', 'primary', 'info'
        confirmText: 'Ya, Lanjutkan',
        cancelText: 'Batal',
        formElement: null,
        onConfirmCallback: null,
        withInput: false,
        inputLabel: '',
        inputName: 'alasan',
        inputPlaceholder: '',
        inputRequired: false,
        inputValue: '',
        inputError: '',

        open(options = {}) {
            this.isAlert = options.isAlert || false;
            this.showCancel = options.showCancel !== undefined ? options.showCancel : !this.isAlert;
            this.title = options.title || (this.isAlert ? 'Pemberitahuan' : 'Konfirmasi Tindakan');
            this.message = options.message || (this.isAlert ? '' : 'Apakah Anda yakin ingin melanjutkan tindakan ini?');
            this.subMessage = options.subMessage || '';
            this.type = options.type || (this.isAlert ? 'info' : 'primary');
            this.confirmText = options.confirmText || (this.isAlert ? 'Mengerti' : (this.type === 'danger' ? 'Ya, Hapus' : (this.type === 'success' ? 'Ya, Setujui' : 'Ya, Lanjutkan')));
            this.cancelText = options.cancelText || 'Batal';
            this.formElement = options.formElement || null;
            this.onConfirmCallback = options.onConfirm || null;
            this.withInput = options.withInput || false;
            this.inputLabel = options.inputLabel || 'Keterangan:';
            this.inputName = options.inputName || 'alasan';
            this.inputPlaceholder = options.inputPlaceholder || '';
            this.inputRequired = options.inputRequired || false;
            this.inputValue = options.inputValue || '';
            this.inputError = '';
            this.isOpen = true;
        },

        cancel() {
            this.isOpen = false;
        },

        confirm() {
            if (this.withInput && this.inputRequired && !this.inputValue.trim()) {
                this.inputError = 'Kolom keterangan wajib diisi.';
                return;
            }

            if (this.formElement) {
                // If there's an input field, attach/update hidden input into the form
                if (this.withInput && this.inputName) {
                    let hiddenInput = this.formElement.querySelector(`input[name="${this.inputName}"], textarea[name="${this.inputName}"]`);
                    if (!hiddenInput) {
                        hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = this.inputName;
                        this.formElement.appendChild(hiddenInput);
                    }
                    hiddenInput.value = this.inputValue;
                }

                this.formElement.dataset.confirmed = 'true';
                this.isOpen = false;
                this.formElement.submit();
                return;
            }

            if (typeof this.onConfirmCallback === 'function') {
                const val = this.inputValue;
                this.isOpen = false;
                this.onConfirmCallback(val);
                return;
            }

            this.isOpen = false;
        }
    };
}

window.universalConfirmModal = universalConfirmModal;

if (window.Alpine) {
    window.Alpine.data('universalConfirmModal', universalConfirmModal);
} else {
    document.addEventListener('alpine:init', () => {
        window.Alpine.data('universalConfirmModal', universalConfirmModal);
    });
}

// Global JavaScript helper API for easy invocation from anywhere
window.openConfirmModal = function(options) {
    window.dispatchEvent(new CustomEvent('open-confirm-modal', { detail: options }));
};

// Global Alert Modal helper (single button info / warning alert)
window.openAlertModal = function(options) {
    if (typeof options === 'string') {
        options = { message: options };
    }
    window.openConfirmModal({
        title: options.title || 'Pemberitahuan',
        message: options.message || '',
        subMessage: options.subMessage || '',
        type: options.type || 'warning',
        confirmText: options.confirmText || 'Mengerti',
        isAlert: true,
        showCancel: false,
        onConfirm: options.onConfirm || null
    });
};

window.showAlert = window.openAlertModal;

// Convenience helpers
window.confirmDelete = function(formElement, title = 'Konfirmasi Hapus Data', message = 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini permanen dan tidak dapat dibatalkan.') {
    window.openConfirmModal({
        formElement: formElement,
        title: title,
        message: message,
        type: 'danger',
        confirmText: 'Ya, Hapus Sekarang'
    });
};

window.confirmApprove = function(formElement, title = 'Konfirmasi Persetujuan', message = 'Apakah Anda yakin ingin menyetujui pengajuan ini?') {
    window.openConfirmModal({
        formElement: formElement,
        title: title,
        message: message,
        type: 'success',
        confirmText: 'Ya, Setujui Sekarang'
    });
};

window.confirmCreate = function(formElement, title = 'Konfirmasi Simpan Data', message = 'Pastikan data yang diinput sudah lengkap dan sesuai. Lanjutkan simpan data?') {
    window.openConfirmModal({
        formElement: formElement,
        title: title,
        message: message,
        type: 'primary',
        confirmText: 'Ya, Simpan Data'
    });
};

// Automatic Form Interceptor for any form with data-confirm="true"
function initUniversalConfirmInterceptor() {
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form && form.matches && form.matches('[data-confirm="true"]') && !form.dataset.confirmed) {
            e.preventDefault();
            window.openConfirmModal({
                formElement: form,
                title: form.dataset.title || 'Konfirmasi Tindakan',
                message: form.dataset.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
                subMessage: form.dataset.submessage || '',
                type: form.dataset.type || 'primary',
                confirmText: form.dataset.confirmText || 'Ya, Lanjutkan',
                cancelText: form.dataset.cancelText || 'Batal',
                withInput: form.dataset.withInput === 'true',
                inputLabel: form.dataset.inputLabel || '',
                inputName: form.dataset.inputName || 'alasan',
                inputPlaceholder: form.dataset.inputPlaceholder || '',
                inputRequired: form.dataset.inputRequired === 'true'
            });
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initUniversalConfirmInterceptor);
} else {
    initUniversalConfirmInterceptor();
}
</script>
