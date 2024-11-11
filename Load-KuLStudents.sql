-- Step 1: Use the specified database
CREATE DATABASE IF NOT EXISTS kuloldstudents
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE kuloldstudents;


-- Step 2: Create a temporary table with VARCHAR for the date column and utf8mb4 encoding
CREATE TABLE IF NOT EXISTS temp_students (
    Voornaam TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    Naam TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    Herkomst TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    Herkomst_actuele_Schrijfwijze TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    Bisdom TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    Datum_Inschrijving VARCHAR(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
);

-- Step 3: Load data from the CSV file into the temporary table with UTF-8 encoding
LOAD DATA INFILE 'D:/Downloads/KuLOldStudents-dateModified.csv'
INTO TABLE temp_students
CHARACTER SET utf8mb4
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES;

-- Step 4: Create the final table with a DATE column for proper storage and utf8mb4 encoding
CREATE TABLE IF NOT EXISTS students (
    Voornaam TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    Naam TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    Herkomst TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    Herkomst_actuele_Schrijfwijze TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    Bisdom TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    Datum_Inschrijving DATE
);

-- Step 5: Transfer data from temp_students to students, converting the date format
INSERT INTO students (Voornaam, Naam, Herkomst, Herkomst_actuele_Schrijfwijze, Bisdom, Datum_Inschrijving)
SELECT
    Voornaam,
    Naam,
    Herkomst,
    Herkomst_actuele_Schrijfwijze,
    Bisdom,
    STR_TO_DATE(Datum_Inschrijving, '%d/%m/%Y')
FROM temp_students;

-- Step 6: Drop the temporary table
DROP TABLE temp_students;

-- Step 7: Verify the import
SELECT * FROM students;
