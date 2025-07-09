-- Add new columns to technicians table
ALTER TABLE technicians
ADD COLUMN email VARCHAR(100) AFTER name,
ADD COLUMN profile_picture VARCHAR(255) AFTER phone,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Update existing records to have default values
UPDATE technicians 
SET email = CONCAT(username, '@example.com')
WHERE email IS NULL;

-- Add unique constraints
ALTER TABLE technicians
ADD UNIQUE INDEX idx_email (email); 