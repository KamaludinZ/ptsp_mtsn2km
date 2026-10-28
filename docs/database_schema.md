# Database Schema for PTSP MTsN 2 KOTA MALANG

## Core Tables

### 1. users
- id (primary key)
- name (string)
- email (string, unique)
- email_verified_at (timestamp, nullable)
- password (string)
- user_type (enum: guru, pegawai, siswa, walimurid, alumni, instansi, umum)
- registration_code (string, nullable) - for internal users
- is_active (boolean, default: true)
- remember_token (string, nullable)
- deleted_at (timestamp, nullable) - soft deletes
- created_at (timestamp)
- updated_at (timestamp)

### 2. roles
- id (primary key)
- name (string)
- guard_name (string, default: 'web')
- created_at (timestamp)
- updated_at (timestamp)

### 3. permissions
- id (primary key)
- name (string)
- guard_name (string, default: 'web')
- created_at (timestamp)
- updated_at (timestamp)

### 4. model_has_permissions
- permission_id (foreign key to permissions.id)
- model_type (string)
- model_id (unsigned big integer)

### 5. model_has_roles
- role_id (foreign key to roles.id)
- model_type (string)
- model_id (unsigned big integer)

### 6. role_has_permissions
- permission_id (foreign key to permissions.id)
- role_id (foreign key to roles.id)

## Service Management Tables

### 7. services
- id (primary key)
- name (string) - Service name
- code (string, unique) - Unique service code
- description (text, nullable)
- user_types_allowed (jsonb, nullable) - Applicant roles the service is open to (App\Support\Applicants::ROLES: siswa, walimurid, alumni, umum, instansi, guru, pegawai), each once in that order; NULL = every applicant. CHECK: NULL or a non-empty array of those roles. Service::applicantRoles() gives the spatie roles
- jenis_sk_id (foreign key to persuratan_masters.id, nullable, null on delete) - Jenis SK bawaan for SK asked for from this service's tickets
- is_active (boolean, default: true)
- created_by (foreign key to users.id)
- deleted_at (timestamp, nullable)
- created_at (timestamp)
- updated_at (timestamp)

### 8. service_requirements
- id (primary key)
- service_id (foreign key to services.id)
- requirement_name (string)
- is_required (boolean, default: true)
- description (text, nullable)
- created_at (timestamp)
- updated_at (timestamp)

### 9. service_components (Implementing Permen PANRB 15/2014)
- id (primary key)
- service_id (foreign key to services.id)
- component_type (enum: 'dasar_hukum', 'persyaratan', 'mekanisme', 'jangka_waktu', 'biaya', 'produk_layanan', 'sarana_prasarana', 'kompetensi_pelaksana', 'pengawasan_internal', 'penanganan_pengaduan', 'jumlah_pelaksana', 'jaminan_pelayanan', 'jaminan_keamanan', 'evaluasi_kinerja')
- content (text)
- created_at (timestamp)
- updated_at (timestamp)

## Ticket Management Tables

### 10. tickets
- id (primary key)
- ticket_number (string, unique) - Format: LAYANAN-YYYYMM-XXX
- user_id (foreign key to users.id) - The applicant
- service_id (foreign key to services.id)
- channel (enum: 'online', 'offline')
- status (enum: 'submitted', 'verified', 'in_process', 'approved', 'rejected', 'completed', 'cancelled')
- current_handler_id (foreign key to users.id, nullable) - Current person handling the ticket
- assigned_to_id (foreign key to users.id, nullable) - Who the ticket is assigned to
- priority (enum: 'low', 'normal', 'high', 'urgent', default: 'normal')
- notes (text, nullable)
- estimated_completion_date (date, nullable)
- actual_completion_date (date, nullable)
- is_urgent (boolean, default: false)
- created_by (foreign key to users.id) - Usually same as user_id, but for offline tickets might be front-desk staff
- deleted_at (timestamp, nullable)
- created_at (timestamp)
- updated_at (timestamp)

### 11. ticket_logs
- id (primary key)
- ticket_id (foreign key to tickets.id)
- action (string) - What action was taken
- performed_by (foreign key to users.id) - Who performed the action
- from_status (string, nullable) - Previous status
- to_status (string, nullable) - New status
- notes (text, nullable)
- created_at (timestamp)

### 12. ticket_files
- id (primary key)
- ticket_id (foreign key to tickets.id)
- file_path (string) - Path to uploaded file
- file_name (string) - Original file name
- file_type (string) - Mime type
- uploaded_by (foreign key to users.id)
- created_at (timestamp)

### 13. ticket_outputs
- id (primary key)
- ticket_id (foreign key to tickets.id)
- output_type (enum: 'digital', 'physical')
- file_path (string, nullable) - Path to output file if digital
- output_description (text, nullable) - Description of output
- is_delivered (boolean, default: false)
- delivery_date (date, nullable)
- delivery_method (enum: 'email', 'whatsapp', 'physical_collection', nullable)
- delivered_to (foreign key to users.id, nullable) - Who received the output
- created_at (timestamp)
- updated_at (timestamp)

## Visitor Management Tables (Front-Desk Module)

### 14. visitors
- id (primary key)
- name (string)
- guest_type (string: 'kunjungan', 'pemohon', default 'kunjungan', CHECK) - Visiting guest, or applicant come for a PTSP service
- applicant_type (string, nullable, CHECK) - Applicant's status from the visitor book form: siswa, wali_murid, alumni, pegawai, umum, instansi_perusahaan
- service_id (foreign key to services.id, nullable, null on delete) - The service an applicant came for (none for "Lainnya")
- phone (string, nullable) - Also checked when the guest checks out from the public visitor book
- email (string, nullable)
- institution (string, nullable) - From/representing which institution
- institution_category (string, nullable)
- purpose (text) - Purpose of visit; for an applicant "Pemohon Layanan: <service>"
- notes (text, nullable)
- is_obscured (boolean, default: false) - Name masked on the public list
- person_to_meet (string) - Who they want to meet
- check_in_time (datetime, indexed)
- check_out_time (datetime, nullable)
- photo_path (string, nullable) - Path to visitor photo
- visitor_card_number (string, unique, nullable)
- status (enum: 'active', 'checked_out', default: 'active') - Follows check_out_time (model hook); CHECK: checked_out exactly when check_out_time is set, and check_out_time >= check_in_time
- created_by (foreign key to users.id) - Staff who registered the visitor
- created_at (timestamp)
- updated_at (timestamp)

### 14a. visitor_checkouts
Riwayat checkout tamu (insert only, trigger `visitor_checkouts_immutable`): the visit records a checkout the same way whoever does it; this says how.
- id (primary key)
- visitor_id (foreign key to visitors.id, cascade on delete)
- method (string: 'petugas', 'mandiri', CHECK) - By an officer, or by the guest from the public visitor book
- checked_out_by (foreign key to users.id, nullable, null on delete) - The officer; always null for 'mandiri' (CHECK)
- ip_address (string, nullable) - Where a guest's own checkout came from
- checked_out_at (timestamp) - Equals visitors.check_out_time
- created_at (timestamp)

## Complaint and Feedback Tables

### 15. complaints
- id (primary key)
- complaint_type (enum: 'complaint', 'suggestion', 'whistleblowing')
- title (string)
- description (text)
- complainant_name (string, nullable) - Name of person making complaint
- complainant_contact (string, nullable) - Contact information
- complainant_email (string, nullable) - Email for follow-up
- user_id (foreign key to users.id, nullable) - If logged in user made complaint
- status (enum: 'submitted', 'in_review', 'in_progress', 'resolved', 'closed', default: 'submitted')
- priority (enum: 'low', 'normal', 'high', 'urgent', default: 'normal')
- assigned_to (foreign key to users.id, nullable) - Who is handling the complaint
- resolution_notes (text, nullable)
- resolved_at (datetime, nullable)
- resolved_by (foreign key to users.id, nullable)
- anonymous (boolean, default: false)
- created_at (timestamp)
- updated_at (timestamp)

### 16. surveys
- id (primary key)
- name (string) - Name of survey (SKM or SPAK)
- description (text, nullable)
- type (enum: 'skm', 'spak', 'other')
- is_active (boolean, default: true)
- start_date (date)
- end_date (date, nullable)
- created_at (timestamp)
- updated_at (timestamp)

### 17. survey_questions
- id (primary key)
- survey_id (foreign key to surveys.id)
- question_text (text)
- question_type (enum: 'rating', 'multiple_choice', 'text', 'yes_no')
- order (integer)
- is_required (boolean, default: true)
- created_at (timestamp)
- updated_at (timestamp)

### 18. survey_responses
- id (primary key)
- survey_id (foreign key to surveys.id)
- user_id (foreign key to users.id, nullable) - For logged in users
- ticket_id (foreign key to tickets.id, nullable) - Associated with ticket
- ip_address (string, nullable) - For anonymous surveys
- completed_at (datetime)
- created_at (timestamp)

### 19. survey_answers
- id (primary key)
- survey_response_id (foreign key to survey_responses.id)
- survey_question_id (foreign key to survey_questions.id)
- answer_text (text, nullable) - For text responses
- rating_value (integer, nullable) - For rating responses (1-5 or 1-10)
- selected_option (string, nullable) - For multiple choice
- created_at (timestamp)

## Workflow Management Tables

### 20. workflows
- id (primary key)
- service_id (foreign key to services.id)
- name (string) - Name of workflow
- description (text, nullable)
- is_active (boolean, default: true)
- created_at (timestamp)
- updated_at (timestamp)

### 21. workflow_steps
- id (primary key)
- workflow_id (foreign key to workflows.id)
- step_number (integer) - Order of the step
- name (string) - Name of the step
- description (text, nullable)
- required_role (string, nullable) - Role required to complete this step
- estimated_duration_days (integer, default: 1)
- is_optional (boolean, default: false)
- created_at (timestamp)
- updated_at (timestamp)

### 22. ticket_workflows
- id (primary key)
- ticket_id (foreign key to tickets.id)
- workflow_id (foreign key to workflows.id)
- current_step_id (foreign key to workflow_steps.id, nullable)
- completed_at (datetime, nullable)
- created_at (timestamp)
- updated_at (timestamp)

### 23. ticket_workflow_steps
- id (primary key)
- ticket_workflow_id (foreign key to ticket_workflows.id)
- workflow_step_id (foreign key to workflow_steps.id)
- assigned_to (foreign key to users.id, nullable) - Who is assigned to this step
- status (enum: 'pending', 'in_progress', 'completed', 'rejected', default: 'pending')
- started_at (datetime, nullable)
- completed_at (datetime, nullable)
- completed_by (foreign key to users.id, nullable)
- notes (text, nullable)
- created_at (timestamp)
- updated_at (timestamp)

## Service Categories and Approval Levels

### 24. service_categories
- id (primary key)
- name (string)
- description (text, nullable)
- parent_id (foreign key to service_categories.id, nullable) - For hierarchical categories
- order (integer, default: 0)
- is_active (boolean, default: true)
- created_at (timestamp)
- updated_at (timestamp)

### 25. service_category_service
- id (primary key)
- service_category_id (foreign key to service_categories.id)
- service_id (foreign key to services.id)
- created_at (timestamp)

## Additional Configuration Tables

### 26. settings
- id (primary key)
- key (string, unique) - Setting key
- value (text) - Setting value
- description (text, nullable)
- created_at (timestamp)
- updated_at (timestamp)

## Register Penomoran SK Tables

Nomor SK berformat `{N} TAHUN {YYYY}`, satu urutan per tahun (1–9999), terpisah dari nomor surat keluar.
Migrasi: `2026_10_27_100000` s.d. `2026_10_28_180000`.

### 27. surat_keputusan
- id (primary key)
- tahun (smallint) - Tahun register; CHECK sama dengan tahun tanggal_surat
- nomor_urut (integer) - CHECK 1–9999; UNIQUE (tahun, nomor_urut)
- nomor_sk (string 30, unique) - CHECK `nomor_urut || ' TAHUN ' || tahun`
- tanggal_surat (date)
- perihal (string) - Tanpa awalan "Tentang"
- jenis_sk (string) - Jenis SK as written on the SK (kept after the master entry is renamed or deleted)
- jenis_sk_id (foreign key to persuratan_masters.id, nullable, null on delete) - The Master Jenis SK entry behind it, for reports
- nama_peminta (string)
- ticket_id (foreign key to tickets.id, nullable, null on delete) - Permohonan terkait
- keterangan (text, nullable)
- berkas_scan (string, nullable) - Private disk path `surat-keputusan/<file>`; CHECK on the folder, partial UNIQUE (one SK per stored scan)
- berkas_scan_nama, berkas_scan_mime (PDF/JPEG/PNG), berkas_scan_ukuran (1 B–10 MB), scan_diunggah_pada, scan_diunggah_oleh (users.id) - Arsip scan; CHECK all empty without a scan
- pembuat_id (foreign key to users.id, nullable), peran_pembuat (string, nullable) - Petugas pencatat and their active role
- kunci_permintaan (string 100, nullable) - Idempotency-Key of the API request; UNIQUE (pembuat_id, kunci_permintaan)
- versi (integer, default 1) - Optimistic lock, +1 on every change
- bulan (smallint, generated) - Month of tanggal_surat; index (tahun, bulan)
- hari_sampai_terarsip (numeric, generated) - Days from the number to the signed scan
- created_at, updated_at (timestamp)
- Triggers: `surat_keputusan_guard` (tahun, nomor_urut, nomor_sk never change; rows leave only through "Hapus"), `surat_keputusan_no_truncate`

### 28. surat_keputusan_sequences
- tahun (primary key)
- last_number (integer) - Last number given that year; CHECK 0–9999. Numbers are never given again, deleted SK included.

### 29. persuratan_masters (type = jenis_sk)
- Master Jenis SK lives in the Master Persuratan table: nama unique per jenis SK ignoring case and spaces, CHECK trimmed and at least 3 characters.
- Changes are logged in activity_log (log `jenis_sk`); the SK register in log `surat_keputusan` (immutable, like every activity_log row).

