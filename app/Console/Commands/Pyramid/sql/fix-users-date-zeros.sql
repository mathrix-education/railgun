-- save current setting of sql_mode
SET @old_sql_mode := @@sql_mode;

-- derive a new value by removing NO_ZERO_DATE and NO_ZERO_IN_DATE
SET @new_sql_mode := @old_sql_mode;
SET @new_sql_mode := TRIM(BOTH ',' FROM REPLACE(CONCAT(',', @new_sql_mode, ','), ',NO_ZERO_DATE,', ','));
SET @new_sql_mode := TRIM(BOTH ',' FROM REPLACE(CONCAT(',', @new_sql_mode, ','), ',NO_ZERO_IN_DATE,', ','));
SET @@sql_mode := @new_sql_mode;

UPDATE users
SET created_at =
      CASE
        WHEN updated_at != '0000-00-00 00:00:00' THEN updated_at
        ELSE NOW()
        END
WHERE created_at = '0000-00-00 00:00:00';

UPDATE users
SET updated_at = NOW()
WHERE updated_at = '0000-00-00 00:00:00';
-- when we are done with required operations, we can revert back
-- to the original sql_mode setting, from the value we saved
SET @@sql_mode := @old_sql_mode;
