- Migration to update assessments table to use assessment_type field
-- This ensures consistency between teacher and student systems

-- Add assessment_type column if it doesn't exist
ALTER TABLE assessments ADD COLUMN IF NOT EXISTS assessment_type VARCHAR(10) DEFAULT 'SA';

-- Copy data from type to assessment_type if type column exists
UPDATE assessments SET assessment_type = type WHERE type IS NOT NULL;

-- Set default assessment_type for any NULL values
UPDATE assessments SET assessment_type = 'SA' WHERE assessment_type IS NULL OR assessment_type = '';

-- Add index for better performance
CREATE INDEX IF NOT EXISTS idx_assessments_type ON assessments(assessment_type);

-- Note: You may want to drop the 'type' column after ensuring all systems use 'assessment_type'
-- DROP COLUMN type; -- Uncomment this line after verification
