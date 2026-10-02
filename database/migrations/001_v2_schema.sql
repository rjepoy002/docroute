-- Apply once to docrxzp_pal_db after taking a database backup.
-- Supports password_hash() output and the legacy grouped-dashboard preference.
ALTER TABLE dr_users MODIFY password VARCHAR(255) NOT NULL;
ALTER TABLE dr_users ADD COLUMN isgroup TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE dr_documents ADD UNIQUE KEY uq_dr_documents_tracking (id_track);
CREATE INDEX idx_dr_logs_tracking_id ON dr_logs (id_track, id);
CREATE INDEX idx_dr_logs_receiver_status ON dr_logs (receiver, status);
CREATE INDEX idx_dr_logs_sender_status ON dr_logs (sender, status);
