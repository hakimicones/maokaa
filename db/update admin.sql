ALTER TABLE admins 
  ADD COLUMN must_change_password TINYINT(1) DEFAULT 1,
  ADD COLUMN role ENUM('admin','editor') DEFAULT 'admin';

UPDATE admins SET must_change_password = 0 WHERE username IN ('admin', 'TC');


