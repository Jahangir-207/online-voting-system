CREATE TABLE IF NOT EXISTS states (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  code VARCHAR(10) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_states_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS districts (
  id INT NOT NULL AUTO_INCREMENT,
  state_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_district_state_name (state_id, name),
  CONSTRAINT fk_district_state FOREIGN KEY (state_id) REFERENCES states (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS constituencies (
  id INT NOT NULL AUTO_INCREMENT,
  district_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  code VARCHAR(30) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_constituency_district_code (district_id, code),
  CONSTRAINT fk_constituency_district FOREIGN KEY (district_id) REFERENCES districts (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS election_types (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(40) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_election_type_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO states (name, code) VALUES ('Bihar', 'BR');
INSERT IGNORE INTO districts (state_id, name)
SELECT id, 'Patna' FROM states WHERE code = 'BR';
INSERT IGNORE INTO constituencies (district_id, name, code)
SELECT d.id, 'Patna Central (Demo Constituency)', 'BR-PATNA-CENTRAL-DEMO'
FROM districts d INNER JOIN states s ON s.id = d.state_id
WHERE s.code = 'BR' AND d.name = 'Patna';
INSERT IGNORE INTO election_types (name) VALUES ('Assembly'), ('Lok Sabha'), ('Panchayat'), ('Municipal');

ALTER TABLE elections
  ADD COLUMN state_id INT NULL,
  ADD COLUMN district_id INT NULL,
  ADD COLUMN constituency_id INT NULL,
  ADD COLUMN election_type_id INT NULL;

ALTER TABLE voters
  ADD COLUMN state_id INT NULL,
  ADD COLUMN district_id INT NULL,
  ADD COLUMN constituency_id INT NULL;

ALTER TABLE candidates
  ADD COLUMN election_id INT NULL;

UPDATE elections e
INNER JOIN states s ON s.code = 'BR'
INNER JOIN districts d ON d.state_id = s.id AND d.name = 'Patna'
INNER JOIN constituencies c ON c.district_id = d.id AND c.code = 'BR-PATNA-CENTRAL-DEMO'
INNER JOIN election_types et ON et.name = 'Assembly'
SET e.state_id = s.id, e.district_id = d.id, e.constituency_id = c.id, e.election_type_id = et.id
WHERE e.state_id IS NULL;

UPDATE voters v
INNER JOIN states s ON s.code = 'BR'
INNER JOIN districts d ON d.state_id = s.id AND d.name = 'Patna'
INNER JOIN constituencies c ON c.district_id = d.id AND c.code = 'BR-PATNA-CENTRAL-DEMO'
SET v.state_id = s.id, v.district_id = d.id, v.constituency_id = c.id
WHERE v.state_id IS NULL;

UPDATE candidates c
INNER JOIN positions p ON p.id = c.position_id
SET c.election_id = p.election_id
WHERE c.election_id IS NULL;

ALTER TABLE elections
  ADD INDEX idx_elections_geography (state_id, district_id, constituency_id, election_type_id),
  ADD CONSTRAINT fk_elections_state FOREIGN KEY (state_id) REFERENCES states (id),
  ADD CONSTRAINT fk_elections_district FOREIGN KEY (district_id) REFERENCES districts (id),
  ADD CONSTRAINT fk_elections_constituency FOREIGN KEY (constituency_id) REFERENCES constituencies (id),
  ADD CONSTRAINT fk_elections_type FOREIGN KEY (election_type_id) REFERENCES election_types (id);

ALTER TABLE voters
  ADD INDEX idx_voters_geography (state_id, district_id, constituency_id),
  ADD CONSTRAINT fk_voters_state FOREIGN KEY (state_id) REFERENCES states (id),
  ADD CONSTRAINT fk_voters_district FOREIGN KEY (district_id) REFERENCES districts (id),
  ADD CONSTRAINT fk_voters_constituency FOREIGN KEY (constituency_id) REFERENCES constituencies (id);

ALTER TABLE candidates
  ADD INDEX idx_candidates_election (election_id),
  ADD CONSTRAINT fk_candidates_election FOREIGN KEY (election_id) REFERENCES elections (id) ON DELETE CASCADE;
