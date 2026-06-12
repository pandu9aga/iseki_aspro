-- ============================================================
-- Add updated_at timestamp to procedures table
-- 
-- Cara pakai:
--   1. Buka phpMyAdmin → pilih database iseki_aspro
--   2. Klik tab SQL
--   3. Paste query di bawah lalu klik Go
--   
--   Atau via command line:
--   mysql -u root -D iseki_aspro < database/sql/add_updated_at_to_procedures.sql
-- ============================================================

ALTER TABLE `procedures` 
ADD COLUMN `updated_at` TIMESTAMP NULL 
DEFAULT CURRENT_TIMESTAMP 
ON UPDATE CURRENT_TIMESTAMP
COMMENT 'Last update timestamp, auto-set by MySQL on INSERT and UPDATE';
