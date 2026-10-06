-- Create database and user for PTSP application
-- Connect as a superuser (like postgres)

-- Create the database user with password
-- Ganti password di bawah dan samakan dengan DB_PASSWORD di .env
CREATE USER ptspmtsn2 WITH PASSWORD 'ganti_password_ini';

-- Create the database and assign ownership to the user
CREATE DATABASE ptspmtsn2 OWNER ptspmtsn2;

-- Grant all privileges on the database to the user
GRANT ALL PRIVILEGES ON DATABASE ptspmtsn2 TO ptspmtsn2;

-- Connect to the newly created database
\c ptspmtsn2

-- Pencarian teks (indeks trigram) butuh extension ini; dibuat oleh superuser
CREATE EXTENSION IF NOT EXISTS pg_trgm;

-- Grant all privileges on schema to the user
GRANT ALL ON SCHEMA public TO ptspmtsn2;

-- Grant all privileges on all tables in schema to the user
GRANT ALL ON ALL TABLES IN SCHEMA public TO ptspmtsn2;

-- Grant all privileges on all sequences in schema to the user
GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO ptspmtsn2;

-- Grant all privileges on all functions in schema to the user
GRANT ALL ON ALL FUNCTIONS IN SCHEMA public TO ptspmtsn2;

-- Set default privileges for future tables
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO ptspmtsn2;

-- Set default privileges for future sequences
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO ptspmtsn2;

-- Set default privileges for future functions
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON FUNCTIONS TO ptspmtsn2;

-- Exit
\q