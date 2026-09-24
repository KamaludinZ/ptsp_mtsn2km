<!-- Modals -->
<div class="modal fade" id="createSurveyModal" tabindex="-1" aria-labelledby="createSurveyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createSurveyModalLabel">Buat Survei Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="create-survey-form">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Jenis Survei</label>
                        <select class="form-select" name="type" required>
                            <option value="">Pilih Jenis</option>
                            <option value="identity">Form Identitas</option>
                            <option value="skm">Survei Kepuasan (SKM)</option>
                            <option value="spak">Survei Anti Korupsi (SPAK)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Survei</label>
                        <input type="text" class="form-control" name="name" placeholder="Contoh: Survei Kepuasan Triwulan I 2025" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Deskripsi survei..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" name="end_date">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for adding identity questions -->
<div class="modal fade" id="addIdentityQuestionModal" tabindex="-1" aria-labelledby="addIdentityQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addIdentityQuestionModalLabel">Tambah Pertanyaan Identitas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="add-identity-question-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="survey_type" value="identity">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pertanyaan</label>
                        <input type="text" class="form-control" name="question" placeholder="Contoh: Nama Lengkap Anda" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Tipe Jawaban</label>
                            <select class="form-select" name="field_type" required>
                                <option value="text">Teks</option>
                                <option value="textarea">Area Teks</option>
                                <option value="select">Pilihan (Dropdown)</option>
                                <option value="radio">Pilihan Tunggal (Radio)</option>
                                <option value="checkbox">Kotak Centang</option>
                                <option value="number">Angka</option>
                                <option value="email">Email</option>
                                <option value="phone">Telepon</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unsur</label>
                            <input type="text" class="form-control" name="unsur" placeholder="Tidak ada unsur untuk tipe identitas" disabled>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Opsi Jawaban (jika ada)</label>
                        <input type="text" class="form-control" name="options" placeholder="Pisahkan dengan koma jika lebih dari satu">
                        <div class="form-text">Contoh: Laki-laki, Perempuan</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_required" value="1" id="identityIsRequired">
                                <label class="form-check-label" for="identityIsRequired">Wajib Diisi</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="identityIsActive" checked>
                                <label class="form-check-label" for="identityIsActive">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for adding SKM questions -->
<div class="modal fade" id="addSkmQuestionModal" tabindex="-1" aria-labelledby="addSkmQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSkmQuestionModalLabel">Tambah Pertanyaan SKM</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="add-skm-question-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="survey_type" value="skm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pertanyaan</label>
                        <textarea class="form-control" name="question" rows="3" placeholder="Contoh: Seberapa puaskah Anda dengan layanan yang diberikan?" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Unsur/Tema</label>
                            <select class="form-select" name="unsur_id" required>
                                <?php $__currentLoopData = $skmUnsurs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unsur): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($unsur->id); ?>"><?php echo e($unsur->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipe Jawaban</label>
                            <select class="form-select" name="field_type" required>
                                <option value="rating">Rating (1-5)</option>
                                <option value="radio">Pilihan Tunggal (Radio)</option>
                                <option value="likert_scale">Skala Likert</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Opsi Jawaban</label>
                        <input type="text" class="form-control" name="options" placeholder="Pisahkan dengan koma">
                        <div class="form-text">Contoh: Sangat Puas, Puas, Cukup, Kurang Puas, Sangat Tidak Puas</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_required" value="1" id="skmIsRequired">
                                <label class="form-check-label" for="skmIsRequired">Wajib Diisi</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="skmIsActive" checked>
                                <label class="form-check-label" for="skmIsActive">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for adding SPAK questions -->
<div class="modal fade" id="addSpakQuestionModal" tabindex="-1" aria-labelledby="addSpakQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSpakQuestionModalLabel">Tambah Pertanyaan SPAK</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="add-spak-question-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="survey_type" value="spak">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pertanyaan</label>
                        <textarea class="form-control" name="question" rows="3" placeholder="Contoh: Apakah ada praktik korupsi yang Anda temui saat mengakses layanan ini?" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Unsur/Tema</label>
                            <select class="form-select" name="unsur_id" required>
                                <?php $__currentLoopData = $spakUnsurs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unsur): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($unsur->id); ?>"><?php echo e($unsur->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipe Jawaban</label>
                            <select class="form-select" name="field_type" required>
                                <option value="radio">Pilihan Tunggal (Ya/Tidak)</option>
                                <option value="likert_scale">Skala Likert</option>
                                <option value="open_ended">Jawaban Terbuka</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Opsi Jawaban</label>
                        <input type="text" class="form-control" name="options" placeholder="Pisahkan dengan koma jika lebih dari satu">
                        <div class="form-text">Contoh: Sangat Rendah, Rendah, Sedang, Tinggi, Sangat Tinggi</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_required" value="1" id="spakIsRequired">
                                <label class="form-check-label" for="spakIsRequired">Wajib Diisi</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="spakIsActive" checked>
                                <label class="form-check-label" for="spakIsActive">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for creating editions -->
<div class="modal fade" id="createEditionModal" tabindex="-1" aria-labelledby="createEditionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createEditionModalLabel">Buat Edisi Survei Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="create-edition-form">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Triwulan</label>
                            <select class="form-select" name="period" required>
                                <option value="Q1">Triwulan 1</option>
                                <option value="Q2">Triwulan 2</option>
                                <option value="Q3">Triwulan 3</option>
                                <option value="Q4">Triwulan 4</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tahun</label>
                            <select class="form-select" name="year" required>
                                <?php for($i = 0; $i < 5; $i++): ?>
                                    <option value="<?php echo e(date('Y') + $i); ?>"><?php echo e(date('Y') + $i); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="createEditionIsActive">
                        <label class="form-check-label" for="createEditionIsActive">
                            Aktifkan Edisi Ini
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for editing questions -->
<div class="modal fade" id="editQuestionModal" tabindex="-1" aria-labelledby="editQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editQuestionModalLabel">Edit Pertanyaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="edit-question-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" id="edit-question-id" name="id">
                <input type="hidden" id="edit-survey-type" name="survey_type">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pertanyaan</label>
                        <input type="text" class="form-control" id="edit-question" name="question" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Tipe Jawaban</label>
                            <select class="form-select" id="edit-field-type" name="field_type" required>
                                <option value="text">Teks</option>
                                <option value="textarea">Area Teks</option>
                                <option value="select">Pilihan (Dropdown)</option>
                                <option value="radio">Pilihan Tunggal (Radio)</option>
                                <option value="checkbox">Kotak Centang</option>
                                <option value="number">Angka</option>
                                <option value="email">Email</option>
                                <option value="phone">Telepon</option>
                                <option value="rating">Rating (1-5)</option>
                                <option value="likert_scale">Skala Likert</option>
                                <option value="open_ended">Jawaban Terbuka</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unsur</label>
                            <select class="form-select" id="edit-unsur-id" name="unsur_id">
                                <!-- Options will be populated dynamically -->
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Opsi Jawaban (jika ada)</label>
                        <input type="text" class="form-control" id="edit-options" name="options" placeholder="Pisahkan dengan koma jika lebih dari satu">
                        <div class="form-text">Contoh: Laki-laki, Perempuan</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_required" value="1" id="edit-is-required">
                                <label class="form-check-label" for="edit-is-required">Wajib Diisi</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit-is-active">
                                <label class="form-check-label" for="edit-is-active">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/admin/survey/partials/modals.blade.php ENDPATH**/ ?>