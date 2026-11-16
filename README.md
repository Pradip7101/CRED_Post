User  (PHP + PostgreSQL)

A simple user details form built with HTML, CSS and PHP 8 (PDO). Data is sent with POST, saved to PostgreSQL, and shown in a table.


Requirements

PHP 8.x with pdo_pgsql enabled
PostgreSQL
XAMPP / any PHP server



Database setup

Create a database in pgAdmin (for example db_pradip), connect to it, and run:

    CREATE TABLE IF NOT EXISTS users (
        id         INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
        full_name  VARCHAR(100) NOT NULL,
        email      VARCHAR(150) NOT NULL UNIQUE,
        phone      VARCHAR(20)  NOT NULL,
        dob        DATE         NOT NULL,
        gender     VARCHAR(10)  NOT NULL CHECK (gender IN ('Male', 'Female', 'Other')),
        address    TEXT         NOT NULL,
        created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
Select in pgadmin

    SELECT * FROM users;


Delete all records where id is '14'

    DELETE FROM users
    WHERE id =14;
