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
- user_types_allowed (json) - Allowed user types: ["guru", "pegawai", "siswa", ...]
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
- institution (string) - From/representing which institution
- purpose (text) - Purpose of visit
- person_to_meet (string) - Who they want to meet
- check_in_time (datetime)
- check_out_time (datetime, nullable)
- photo_path (string, nullable) - Path to visitor photo
- visitor_card_number (string, unique, nullable)
- status (enum: 'active', 'checked_out', default: 'active')
- created_by (foreign key to users.id) - Staff who registered the visitor
- created_at (timestamp)
- updated_at (timestamp)

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