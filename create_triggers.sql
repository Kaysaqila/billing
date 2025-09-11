-- create_triggers.sql
-- Run this file in your MySQL / phpMyAdmin to create triggers
-- These triggers ensure status_bayar is set to 'Belum Lunas' when tagihan > 0

DELIMITER //

DROP TRIGGER IF EXISTS pelanggan_before_insert;//
CREATE TRIGGER pelanggan_before_insert
BEFORE INSERT ON pelanggan
FOR EACH ROW
BEGIN
  IF NEW.tagihan IS NULL THEN
    SET NEW.status_bayar = 'Lunas';
  ELSEIF NEW.tagihan > 0 THEN
    SET NEW.status_bayar = 'Belum Lunas';
  ELSE
    SET NEW.status_bayar = 'Lunas';
  END IF;
END;//

DROP TRIGGER IF EXISTS pelanggan_before_update;//
CREATE TRIGGER pelanggan_before_update
BEFORE UPDATE ON pelanggan
FOR EACH ROW
BEGIN
  IF NEW.tagihan IS NULL THEN
    SET NEW.status_bayar = 'Lunas';
  ELSEIF NEW.tagihan > 0 THEN
    SET NEW.status_bayar = 'Belum Lunas';
  ELSE
    SET NEW.status_bayar = 'Lunas';
  END IF;
END;//

DELIMITER ;

-- Notes:
-- 1) Execute this file using phpMyAdmin SQL tab or the mysql CLI:
--    mysql -u root -p your_database_name < create_triggers.sql
-- 2) If your column types store tagihan as varchar, make sure values are numeric.
-- 3) These triggers enforce the rule at DB level; the PHP file is also updated to set status before UPDATE.
