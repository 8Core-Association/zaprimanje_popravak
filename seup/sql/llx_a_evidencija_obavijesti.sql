-- =============================================================================
-- TABLE: llx_a_evidencija_obavijesti
-- Purpose: User-level tracking for system notifications (read/dismiss status)
-- =============================================================================
-- Tablica za evidenciju korisničkih interakcija s obavijestima
-- (pročitano, odbačeno) - svaki korisnik ima vlastiti tracking
-- =============================================================================

CREATE TABLE IF NOT EXISTS llx_a_evidencija_obavijesti (
    rowid INT(11) NOT NULL AUTO_INCREMENT,

    -- UUID obavijesti s centralnog servera
    notification_uuid VARCHAR(50) NOT NULL COMMENT 'Unique identifier from central notification server',

    -- Veza na korisnika
    fk_user INT(11) NOT NULL COMMENT 'Reference to llx_user',

    -- Status obavijesti
    procitano TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Has user read the notification (0=no, 1=yes)',
    datum_citanja DATETIME DEFAULT NULL COMMENT 'When notification was marked as read',

    odbaceno TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Has user dismissed notification forever (0=no, 1=yes)',
    datum_odbacivanja DATETIME DEFAULT NULL COMMENT 'When notification was dismissed',

    -- Metadata
    datum_prvog_prikaza DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When notification was first shown to user',
    entity INT(11) NOT NULL DEFAULT 1 COMMENT 'Multi-company entity',

    PRIMARY KEY (rowid),
    UNIQUE KEY uk_obavijest_korisnik (notification_uuid, fk_user),
    KEY idx_fk_user (fk_user),
    KEY idx_procitano (procitano),
    KEY idx_odbaceno (odbaceno),
    KEY idx_uuid (notification_uuid),

    CONSTRAINT fk_evidencija_obavijesti_user FOREIGN KEY (fk_user)
        REFERENCES llx_user(rowid) ON DELETE CASCADE ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tracking korisničkih interakcija s obavijestima (pročitano/odbačeno)';
