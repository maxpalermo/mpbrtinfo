DELIMITER //

DROP PROCEDURE IF EXISTS `convert_database_charset_collate`//

CREATE PROCEDURE `convert_database_charset_collate`(
    IN p_database_name VARCHAR(64),
    IN p_charset VARCHAR(32),
    IN p_collation VARCHAR(64)
)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE table_name VARCHAR(64);
    DECLARE current_sql_mode VARCHAR(1000);
    DECLARE cur CURSOR FOR
        SELECT TABLE_NAME
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = p_database_name;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Save current SQL mode
    SELECT @@sql_mode INTO current_sql_mode;
    
    -- Set a safer SQL mode for the conversion process
    SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
    
    -- Set default database charset and collation
    SET @alter_db_sql = CONCAT('ALTER DATABASE `', p_database_name, '` CHARACTER SET ', p_charset, ' COLLATE ', p_collation);
    PREPARE stmt FROM @alter_db_sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    
    -- Convert each table
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO table_name;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Convert table to specified charset and collation
        SET @alter_table_sql = CONCAT('ALTER TABLE `', p_database_name, '`.`', table_name, '` CONVERT TO CHARACTER SET ', p_charset, ' COLLATE ', p_collation);
        PREPARE stmt FROM @alter_table_sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
        
        -- Log the conversion (optional)
        SELECT CONCAT('Converted table: ', table_name, ' to charset: ', p_charset, ' and collation: ', p_collation) AS 'Conversion Progress';
    END LOOP;
    
    CLOSE cur;
    
    -- Restore original SQL mode
    SET SESSION sql_mode = current_sql_mode;
    
    SELECT CONCAT('All tables in database `', p_database_name, '` have been converted to charset: ', p_charset, ' and collation: ', p_collation) AS 'Conversion Complete';
END//

DELIMITER ;

-- Esempio di utilizzo:
-- CALL convert_database_charset_collate('nome_database', 'utf8mb4', 'utf8mb4_unicode_ci');
