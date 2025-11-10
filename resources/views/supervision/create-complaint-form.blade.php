@extends('supervision.layout')

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">Formulir Pengaduan</h1>

                    <!-- Tab Navigation -->
                    <div class="mb-4 border-b border-gray-200">
                        <ul class="flex flex-wrap -mb-px" id="complaintTabs" role="tablist">
                            <li class="mr-2" role="presentation">
                                <button class="inline-block p-4 border-b-2 rounded-t-lg" id="dumas-tab" data-tabs-target="#dumas" type="button" role="tab" aria-controls="dumas" aria-selected="true">Pengaduan / Saran</button>
                            </li>
                            <li class="mr-2" role="presentation">
                                <button class="inline-block p-4 border-b-2 rounded-t-lg" id="whistleblowing-tab" data-tabs-target="#whistleblowing" type="button" role="tab" aria-controls="whistleblowing" aria-selected="false">Whistleblowing</button>
                            </li>
                        </ul>
                    </div>

                    <!-- Tab Content -->
                    <div id="complaintTabsContent">
                        <!-- Dumas Tab -->
                        <div class="hidden" id="dumas" role="tabpanel" aria-labelledby="dumas-tab">
                            <form method="POST" action="{{ route('supervision.complaint.submit') }}">
                                @csrf
                                <input type="hidden" name="complaint_origin" value="dumas">
                                <div class="mb-4">
                                    <label for="complaint_type_dumas" class="block text-sm font-medium text-gray-700">Jenis</label>
                                    <select name="complaint_type" id="complaint_type_dumas" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="">Pilih Jenis</option>
                                        <option value="complaint" {{ old('complaint_type') === 'complaint' ? 'selected' : '' }}>Pengaduan</option>
                                        <option value="suggestion" {{ old('complaint_type') === 'suggestion' ? 'selected' : '' }}>Saran</option>
                                    </select>
                                </div>
                                @include('supervision.partials.complaint-form-fields')
                                <div class="flex justify-end">
                                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                        Kirim
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Whistleblowing Tab -->
                        <div class="hidden" id="whistleblowing" role="tabpanel" aria-labelledby="whistleblowing-tab">
                            <form method="POST" action="{{ route('supervision.complaint.submit') }}">
                                @csrf
                                <input type="hidden" name="complaint_type" value="whistleblowing">
                                <input type="hidden" name="complaint_origin" value="whistleblowing">
                                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                                    <p class="font-bold">Penting!</p>
                                    <p>Formulir ini khusus untuk melaporkan pelanggaran serius seperti korupsi, penipuan, atau penyalahgunaan wewenang. Kerahasiaan Anda dijamin.</p>
                                </div>
                                @include('supervision.partials.complaint-form-fields', ['is_whistleblowing' => true])
                                <div class="flex justify-end">
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded">
                                        Kirim Laporan Rahasia
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('[data-tabs-target]');
            const tabContents = document.querySelectorAll('[role="tabpanel"]');

            tabs.forEach(tab => {
                tab.addEventListener('click', function () {
                    const target = document.querySelector(this.dataset.tabsTarget);

                    tabContents.forEach(tc => {
                        tc.classList.add('hidden');
                    });
                    target.classList.remove('hidden');

                    tabs.forEach(t => {
                        t.setAttribute('aria-selected', 'false');
                        t.classList.remove('border-blue-500', 'text-blue-600');
                        t.classList.add('hover:text-gray-600', 'hover:border-gray-300');
                    });

                    this.setAttribute('aria-selected', 'true');
                    this.classList.add('border-blue-500', 'text-blue-600');
                    this.classList.remove('hover:text-gray-600', 'hover:border-gray-300');
                });
            });

            // Activate the first tab by default
            const firstTab = document.querySelector('[role="tab"]');
            if (firstTab) {
                firstTab.click();
            }

            // Anonymous checkbox logic
            const anonymousCheckboxes = document.querySelectorAll('.anonymous-checkbox');
            anonymousCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const isWhistleblowing = this.id.includes('whistleblowing');
                    const identitySection = document.getElementById('identity-section-' + (isWhistleblowing ? 'whistleblowing' : 'dumas'));
                    if (this.checked) {
                        identitySection.classList.add('hidden');
                    } else {
                        identitySection.classList.remove('hidden');
                    }
                });
            });
        });
    </script>
@endsection