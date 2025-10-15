-- Migration: Add new fields to student_fees table for enhanced fee tracking
ALTER TABLE student_fees
  ADD COLUMN total_fee DECIMAL(10,2) AFTER year,
  ADD COLUMN amount_paid DECIMAL(10,2) AFTER total_fee,
  ADD COLUMN remaining_fee DECIMAL(10,2) AFTER amount_paid,
  ADD COLUMN receipt_number VARCHAR(50) AFTER remaining_fee;
