-- Add new columns to admins table
ALTER TABLE admins
ADD COLUMN name VARCHAR(100) AFTER username,
ADD COLUMN email VARCHAR(100) AFTER name,
ADD COLUMN phone VARCHAR(20) AFTER email,
ADD COLUMN profile_picture VARCHAR(255) AFTER phone,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Update existing records to have default values
UPDATE admins 
SET name = username,
    email = CONCAT(username, '@example.com'),
    phone = '0000000000'
WHERE name IS NULL;

-- Add unique constraints
ALTER TABLE admins
ADD UNIQUE INDEX idx_username (username),
ADD UNIQUE INDEX idx_email (email); 