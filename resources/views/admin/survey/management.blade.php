@extends('layouts.admin')

@section('title', 'Manajemen Survey')



@section('content')
<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="flex flex-wrap justify-between items-center mb-4">
        <div>
            <h2 class="text-2xl font-bold text-base-content">
                <i class="bi bi-clipboard-data-fill mr-2 text-secondary"></i>Manajemen Survey
            </h2>
            <p class="text-base-content/70">Kelola sistem survei untuk evaluasi kinerja pelayanan.</p>
        </div>
        <div class="mt-3 md:mt-0">
            <a href="{{ route('suadmin.skm.report') }}" class="btn btn-outline btn-primary shadow-sm">
                <i class="bi bi-file-earmark-bar-graph-fill mr-2"></i>Laporan SKM
            </a>
            <a href="{{ route('suadmin.spak.report') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-shield-check mr-2"></i>Laporan SPAK
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <!-- Survey Categories Tabs -->
            <div class="tabs tabs-boxed mb-4">
                <a class="tab survey-tab-button" :class="{ 'tab-active': activeTab === 'identity' }" @click.prevent="activeTab = 'identity'">
                    <i class="fas fa-user-edit mr-2"></i>Form Identitas
                </a>
                <a class="tab survey-tab-button" :class="{ 'tab-active': activeTab === 'skm' }" @click.prevent="activeTab = 'skm'">
                    <i class="fas fa-smile-beam mr-2"></i>Form SKM
                </a>
                <a class="tab survey-tab-button" :class="{ 'tab-active': activeTab === 'spak' }" @click.prevent="activeTab = 'spak'">
                    <i class="fas fa-shield-alt mr-2"></i>Form SPAK
                </a>
                <a class="tab survey-tab-button" :class="{ 'tab-active': activeTab === 'editions' }" @click.prevent="activeTab = 'editions'">
                    <i class="fas fa-calendar-alt mr-2"></i>Edisi Survei
                </a>
            </div>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Identity Questions Tab -->
                <div x-show="activeTab === 'identity'">
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-header bg-primary-soft flex justify-between items-center">
                            <h5 class="card-title mb-0"><i class="fas fa-user-edit mr-2"></i>Form Identitas Responden</h5>
                            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addIdentityQuestionModal">
                                <i class="fas fa-plus mr-1"></i>Tambah Pertanyaan
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="overflow-x-auto">
                                <table class="table w-full">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Pertanyaan</th>
                                            <th>Unsur</th>
                                            <th>Type Form</th>
                                            <th>Tipe Jawaban</th>
                                            <th>Wajib?</th>
                                            <th>Aktif?</th>
                                            <th class="text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="identity-questions">
                                        @forelse($identityQuestions as $question)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $question->question }}</td>
                                                <td>-</td>
                                                <td><span class="badge badge-info">Identitas</span></td>
                                                <td><span class="badge badge-neutral">{{ $question->field_type }}</span></td>
                                                <td><span class="badge @if($question->is_required) badge-error @else badge-neutral @endif">{{ $question->is_required ? 'Ya' : 'Tidak' }}</span></td>
                                                <td><span class="badge @if($question->is_active) badge-success @else badge-neutral @endif">{{ $question->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                                <td class="text-right">
                                                    <div class="dropdown dropdown-end">
                                                        <label tabindex="0" class="btn btn-ghost btn-xs">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </label>
                                                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-32">
                                                            <li><button class="btn btn-sm btn-warning" onclick="editQuestion('identity', {{ $question->id }})"><i class="fas fa-edit"></i> Edit</button></li>
                                                            <li><button class="btn btn-sm btn-error" onclick="deleteQuestion({{ $question->id }})"><i class="fas fa-trash"></i> Hapus</button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-16">
                                                    <i class="fas fa-inbox fa-5x text-base-content/20 mb-3"></i>
                                                    <h5 class="text-lg font-bold text-base-content/70">Tidak ada pertanyaan identitas yang aktif.</h5>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SKM Questions Tab -->
                <div x-show="activeTab === 'skm'">
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-header bg-success-soft flex justify-between items-center">
                            <h5 class="card-title mb-0"><i class="fas fa-smile-beam mr-2"></i>Form Survei Kepuasan (SKM)</h5>
                            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addSkmQuestionModal">
                                <i class="fas fa-plus mr-1"></i>Tambah Pertanyaan
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="overflow-x-auto">
                                <table class="table w-full">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Pertanyaan</th>
                                            <th>Unsur</th>
                                            <th>Type Form</th>
                                            <th>Tipe Jawaban</th>
                                            <th>Opsi Jawaban</th>
                                            <th>Wajib</th>
                                            <th>Aktif</th>
                                            <th class="text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="skm-questions">
                                        @forelse($skmQuestions as $question)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $question->question }}</td>
                                                <td>{{ $question->unsur->name ?? '-' }}</td>
                                                <td><span class="badge badge-success">SKM</span></td>
                                                <td>{{ $question->field_type }}</td>
                                                <td>{{ $question->options ? implode(', ', json_decode($question->options)) : '-' }}</td>
                                                <td><span class="badge @if($question->is_required) badge-error @else badge-neutral @endif">{{ $question->is_required ? 'Ya' : 'Tidak' }}</span></td>
                                                <td><span class="badge @if($question->is_active) badge-success @else badge-neutral @endif">{{ $question->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                                <td class="text-right">
                                                    <div class="dropdown dropdown-end">
                                                        <label tabindex="0" class="btn btn-ghost btn-xs">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </label>
                                                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-32">
                                                            <li><button class="btn btn-sm btn-warning" onclick="editQuestion('skm', {{ $question->id }})"><i class="fas fa-edit"></i> Edit</button></li>
                                                            <li><button class="btn btn-sm btn-error" onclick="deleteQuestion({{ $question->id }})"><i class="fas fa-trash"></i> Hapus</button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center py-16">
                                                    <i class="fas fa-inbox fa-5x text-base-content/20 mb-3"></i>
                                                    <h5 class="text-lg font-bold text-base-content/70">Tidak ada pertanyaan SKM yang aktif.</h5>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SPAK Questions Tab -->
                <div x-show="activeTab === 'spak'">
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-header bg-info-soft flex justify-between items-center">
                            <h5 class="card-title mb-0"><i class="fas fa-shield-alt mr-2"></i>Form Survei Anti Korupsi (SPAK)</h5>
                            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addSpakQuestionModal">
                                <i class="fas fa-plus mr-1"></i>Tambah Pertanyaan
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="overflow-x-auto">
                                <table class="table w-full">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Pertanyaan</th>
                                            <th>Unsur</th>
                                            <th>Type Form</th>
                                            <th>Tipe Jawaban</th>
                                            <th>Opsi Jawaban</th>
                                            <th>Wajib</th>
                                            <th>Aktif</th>
                                            <th class="text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="spak-questions">
                                        @forelse($spakQuestions as $question)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $question->question }}</td>
                                                <td>{{ $question->unsur->name ?? '-' }}</td>
                                                <td><span class="badge badge-error">SPAK</span></td>
                                                <td>{{ $question->field_type }}</td>
                                                <td>{{ $question->options ? implode(', ', json_decode($question->options)) : '-' }}</td>
                                                <td><span class="badge @if($question->is_required) badge-error @else badge-neutral @endif">{{ $question->is_required ? 'Ya' : 'Tidak' }}</span></td>
                                                <td><span class="badge @if($question->is_active) badge-success @else badge-neutral @endif">{{ $question->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                                <td class="text-right">
                                                    <div class="dropdown dropdown-end">
                                                        <label tabindex="0" class="btn btn-ghost btn-xs">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </label>
                                                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-32">
                                                            <li><button class="btn btn-sm btn-warning" onclick="editQuestion('spak', {{ $question->id }})"><i class="fas fa-edit"></i> Edit</button></li>
                                                            <li><button class="btn btn-sm btn-error" onclick="deleteQuestion({{ $question->id }})"><i class="fas fa-trash"></i> Hapus</button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-16">
                                                    <i class="fas fa-inbox fa-5x text-base-content/20 mb-3"></i>
                                                    <h5 class="text-lg font-bold text-base-content/70">Tidak ada pertanyaan SPAK yang aktif.</h5>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Survey Editions Tab -->
                <div x-show="activeTab === 'editions'">
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-header bg-warning-soft flex justify-between items-center">
                            <h5 class="card-title mb-0"><i class="fas fa-calendar-alt mr-2"></i>Edisi Survei</h5>
                            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#createEditionModal">
                                <i class="fas fa-plus mr-1"></i>Tambah Edisi
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="overflow-x-auto">
                                <table class="table w-full">
                                    <thead>
                                        <tr>
                                            <th>Nama Edisi</th>
                                            <th>Jenis</th>
                                            <th>Periode</th>
                                            <th>Tahun</th>
                                            <th>Tanggal Mulai</th>
                                            <th>Tanggal Selesai</th>
                                            <th>Status</th>
                                            <th class="text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="survey-editions">
                                        @if($activeEdition)
                                            <tr>
                                                <td>{{ $activeEdition->name }}</td>
                                                <td><span class="badge badge-primary">{{ $activeEdition->type }}</span></td>
                                                <td>{{ $activeEdition->period }}</td>
                                                <td>{{ $activeEdition->year }}</td>
                                                <td>{{ $activeEdition->start_date->format('d M Y') }}</td>
                                                <td>{{ $activeEdition->end_date->format('d M Y') }}</td>
                                                <td><span class="badge badge-success">Aktif</span></td>
                                                <td class="text-right">
                                                    <div class="dropdown dropdown-end">
                                                        <label tabindex="0" class="btn btn-ghost btn-xs">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </label>
                                                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-32">
                                                            <li><button class="btn btn-sm btn-warning" onclick="editEdition({{ $activeEdition->id }})"><i class="fas fa-edit"></i> Edit</button></li>
                                                            <li><button class="btn btn-sm btn-error" onclick="deleteEdition({{ $activeEdition->id }})"><i class="fas fa-trash"></i> Hapus</button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td colspan="8" class="text-center py-16">
                                                    <i class="fas fa-calendar-times fa-5x text-base-content/20 mb-3"></i>
                                                    <h5 class="text-lg font-bold text-base-content/70">Tidak ada edisi survei yang aktif saat ini.</h5>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('admin.survey.partials.modals')
@endsection

@push('scripts')
<script>
    $(document).ready(function(){
        $('button[data-bs-toggle="tab"]').on('show.bs.tab', function(e) {
            localStorage.setItem('activeTab', $(e.target).attr('data-bs-target'));
        });
        var activeTab = localStorage.getItem('activeTab');
        if(activeTab){
            $('#surveyTabs button[data-bs-target="' + activeTab + '"]').tab('show');
        }
    });

    // Handle create edition form submission
    $('#create-edition-form').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: '{{ route('suadmin.survey.api.editions.create') }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#createEditionModal').modal('hide');
                    // You might want to reload the page or update the table dynamically
                    location.reload();
                }
            },
            error: function(xhr) {
                // Handle errors
                console.log(xhr.responseText);
            }
        });
    });

    // Handle edit edition
    function editEdition(id) {
        // You will need to create a modal for editing editions
        // and populate it with the data from the server
        // Then, you can use a similar AJAX request to update the edition
    }

    // Handle delete edition
    function deleteEdition(id) {
        if (confirm('Are you sure you want to delete this edition?')) {
            $.ajax({
                url: `/suadmin/survey/api/editions/${id}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        // You might want to reload the page or update the table dynamically
                        location.reload();
                    }
                },
                error: function(xhr) {
                    // Handle errors
                    console.log(xhr.responseText);
                }
            });
        }
    }

    // Handle add identity question form submission
    $('#add-identity-question-form').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: '{{ route('suadmin.survey.api.questions.create') }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#addIdentityQuestionModal').modal('hide');
                    location.reload();
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
            }
        });
    });

    // Handle add SKM question form submission
    $('#add-skm-question-form').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: '{{ route('suadmin.survey.api.questions.create') }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#addSkmQuestionModal').modal('hide');
                    location.reload();
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
            }
        });
    });

    // Handle add SPAK question form submission
    $('#add-spak-question-form').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: '{{ route('suadmin.survey.api.questions.create') }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#addSpakQuestionModal').modal('hide');
                    location.reload();
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
            }
        });
    });

    function editQuestion(type, id) {
        $.ajax({
            url: `/suadmin/survey/api/questions/${id}`,
            method: 'GET',
            success: function(response) {
                $('#edit-question-id').val(response.id);
                $('#edit-survey-type').val(response.survey_type);
                $('#edit-question').val(response.question);
                $('#edit-field-type').val(response.field_type);
                $('#edit-options').val(response.options ? JSON.parse(response.options).join(', ') : '');
                $('#edit-is-required').prop('checked', response.is_required);
                $('#edit-is-active').prop('checked', response.is_active);

                const unsurSelect = $('#edit-unsur-id');
                unsurSelect.empty();
                if (response.survey_type === 'identity') {
                    unsurSelect.append('<option value="">Tidak ada unsur untuk tipe identitas</option>');
                    unsurSelect.prop('disabled', true);
                } else {
                    unsurSelect.prop('disabled', false);
                    const unsurs = response.survey_type === 'skm' ? {!! json_encode($skmUnsurs) !!} : {!! json_encode($spakUnsurs) !!};
                    unsurs.forEach(unsur => {
                        unsurSelect.append(`<option value="${unsur.id}">${unsur.name}</option>`);
                    });
                    if (response.unsur_id) {
                        unsurSelect.val(response.unsur_id);
                    }
                }

                $('#editQuestionModal').modal('show');
            },
            error: function(xhr) {
                console.log(xhr.responseText);
            }
        });
    }

    $('#edit-question-form').on('submit', function(e) {
        e.preventDefault();
        const id = $('#edit-question-id').val();
        const formData = new FormData(this);
        $.ajax({
            url: `/suadmin/survey/api/questions/${id}`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#editQuestionModal').modal('hide');
                    location.reload();
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
            }
        });
    });

    function deleteQuestion(id) {
        if (confirm('Are you sure you want to delete this question?')) {
            $.ajax({
                url: `/suadmin/survey/api/questions/${id}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }
    }
</script>
@endpush


