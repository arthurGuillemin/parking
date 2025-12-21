-- Tables correspondant au code PHP (English naming, UUIDs for users/owners)

CREATE TABLE IF NOT EXISTS users (
  id VARCHAR(36) PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  first_name VARCHAR(255) NOT NULL,
  last_name VARCHAR(255) NOT NULL,
  creation_date DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS owners (
  id VARCHAR(36) PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  first_name VARCHAR(255) NOT NULL,
  last_name VARCHAR(255) NOT NULL,
  creation_date DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS parkings (
  id INT PRIMARY KEY AUTO_INCREMENT,
  owner_id VARCHAR(36) NOT NULL, -- UUID references owners.id, but foreign keys might fail if types differ. ensuring VARCHAR(36)
  name VARCHAR(255) NOT NULL,
  address VARCHAR(255) NOT NULL,
  latitude DECIMAL(10, 8) NOT NULL,
  longitude DECIMAL(11, 8) NOT NULL,
  total_capacity INT NOT NULL,
  open_24_7 BOOLEAN DEFAULT FALSE NOT NULL,
  FOREIGN KEY (owner_id) REFERENCES owners(id)
);

CREATE TABLE IF NOT EXISTS subscription_types (
  id INT PRIMARY KEY AUTO_INCREMENT,
  parking_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT NULL,
  FOREIGN KEY (parking_id) REFERENCES parkings(id)
);

CREATE TABLE IF NOT EXISTS subscriptions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id VARCHAR(36) NOT NULL,
  parking_id INT NOT NULL,
  type_id INT NULL,
  start_date DATE NOT NULL,
  end_date DATE NULL,
  status VARCHAR(50) NOT NULL,
  monthly_price DECIMAL(10, 2) NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (parking_id) REFERENCES parkings(id),
  FOREIGN KEY (type_id) REFERENCES subscription_types(id)
);

-- Note: In the code, SubscriptionSlotRepository uses 'subscription_id' to filter by type.
-- Assuming 'subscription_id' references 'subscription_types' to define slots for a type.
CREATE TABLE IF NOT EXISTS subscription_slots (
  id INT PRIMARY KEY AUTO_INCREMENT,
  subscription_type_id INT NOT NULL,
  weekday TINYINT NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  FOREIGN KEY (subscription_type_id) REFERENCES subscription_types(id)
);

CREATE TABLE IF NOT EXISTS reservations (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id VARCHAR(36) NOT NULL,
  parking_id INT NOT NULL,
  start_datetime DATETIME NOT NULL,
  end_datetime DATETIME NOT NULL,
  status VARCHAR(50) NOT NULL,
  calculated_amount DECIMAL(10, 2) NOT NULL,
  final_amount DECIMAL(10, 2) NULL,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (parking_id) REFERENCES parkings(id)
);

CREATE TABLE IF NOT EXISTS parking_sessions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id VARCHAR(36) NOT NULL,
  parking_id INT NOT NULL,
  reservation_id INT NULL,
  entry_time DATETIME NOT NULL,
  exit_time DATETIME NULL,
  final_amount DECIMAL(10, 2) NULL,
  penalty_applied BOOLEAN DEFAULT FALSE NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (parking_id) REFERENCES parkings(id),
  FOREIGN KEY (reservation_id) REFERENCES reservations(id)
);

CREATE TABLE IF NOT EXISTS pricing_rules (
  id INT PRIMARY KEY AUTO_INCREMENT,
  parking_id INT NOT NULL,
  start_duration_minute INT NOT NULL,
  end_duration_minute INT NULL,
  price_per_slice DECIMAL(10, 2) NOT NULL,
  slice_in_minutes INT DEFAULT 15 NOT NULL,
  effective_date DATETIME NOT NULL,
  FOREIGN KEY (parking_id) REFERENCES parkings(id)
);

CREATE TABLE IF NOT EXISTS opening_hours (
  id INT PRIMARY KEY AUTO_INCREMENT,
  parking_id INT NOT NULL,
  weekday TINYINT NOT NULL,
  opening_time TIME NOT NULL,
  closing_time TIME NOT NULL,
  FOREIGN KEY (parking_id) REFERENCES parkings(id)
);

CREATE TABLE IF NOT EXISTS invoices (
  id INT PRIMARY KEY AUTO_INCREMENT,
  reservation_id INT NULL,
  session_id INT NULL,
  issue_date DATETIME NOT NULL,
  amount_ht DECIMAL(10, 2) NOT NULL,
  amount_ttc DECIMAL(10, 2) NOT NULL,
  details TEXT NULL,
  invoice_type VARCHAR(50) NOT NULL,
  FOREIGN KEY (reservation_id) REFERENCES reservations(id),
  FOREIGN KEY (session_id) REFERENCES parking_sessions(id)
);

