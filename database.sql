DROP TABLE IF EXISTS AssignmentFile;
DROP TABLE IF EXISTS Sectionassignment;
DROP TABLE IF EXISTS ClassSection;
DROP TABLE IF EXISTS ClassStudent;
DROP TABLE IF EXISTS Assignment;
DROP TABLE IF EXISTS Section;
DROP TABLE IF EXISTS Class;
DROP TABLE IF EXISTS User;
DROP TABLE IF EXISTS File;

-- Création de la table User
CREATE TABLE User (
    id CHAR(36) PRIMARY KEY NOT NULL DEFAULT (UUID()),
    role ENUM('admin', 'teacher', 'student') NOT NULL,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT 0,
    reset_token varchar(255) DEFAULT NULL,
    reset_token_expires_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Création de la table File
CREATE TABLE File (
    id CHAR(36) PRIMARY KEY NOT NULL DEFAULT (UUID()),
    token VARCHAR(255) UNIQUE,
    name VARCHAR(255) NOT NULL,
    extension VARCHAR(10),
    size FLOAT NOT NULL,
    user_id CHAR(36) NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE

);
-- Création de la table Class
CREATE TABLE Class (
    id CHAR(36) PRIMARY KEY NOT NULL DEFAULT (UUID()),
        teacher_id CHAR(36) NOT NULL,
        file_id CHAR(36) DEFAULT NULL,
        color VARCHAR(7) DEFAULT '#ffffff',
        name VARCHAR(50) NOT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (teacher_id) REFERENCES User(id) ON DELETE CASCADE,
        FOREIGN KEY (file_id) REFERENCES File(id) ON DELETE SET NULL
);

-- Création de la table ClassStudent (relation entre élèves et classes)
CREATE TABLE ClassStudent (
    user_id CHAR(36) NOT NULL,
    class_id CHAR(36) NOT NULL,
    PRIMARY KEY (user_id, class_id),
    FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES Class(id) ON DELETE CASCADE
);

-- Création de la table Section
CREATE TABLE Section (
    id CHAR(36) PRIMARY KEY NOT NULL DEFAULT (UUID()),
    name VARCHAR(255) NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Création de la table assignment
CREATE TABLE Assignment (
    id CHAR(36) PRIMARY KEY NOT NULL DEFAULT (UUID()),
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    start_period DATETIME NOT NULL,
    end_period DATETIME NOT NULL,
    max_files INT DEFAULT 1,
    allow_late_submission BOOLEAN DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
-- Création de la table ClassSection (relation entre classes et sections)
CREATE TABLE ClassSection (
    section_id CHAR(36) NOT NULL,
    class_id CHAR(36) NOT NULL,
    PRIMARY KEY (section_id, class_id),
    FOREIGN KEY (section_id) REFERENCES Section(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES Class(id) ON DELETE CASCADE
);

-- Création de la table Sectionassignment (relation entre sections et devoirs)
CREATE TABLE SectionAssignment (
    section_id CHAR(36) NOT NULL,
    assignment_id CHAR(36) NOT NULL,
    PRIMARY KEY (section_id, assignment_id),
    FOREIGN KEY (section_id) REFERENCES Section(id) ON DELETE CASCADE,
    FOREIGN KEY (assignment_id) REFERENCES Assignment(id) ON DELETE CASCADE
);

-- Création de la table assignmentFile (relation entre devoirs et fichiers)
CREATE TABLE AssignmentFile (
    assignment_id CHAR(36) NOT NULL,
    file_id CHAR(36) NOT NULL,
    PRIMARY KEY (assignment_id, file_id),
    FOREIGN KEY (assignment_id) REFERENCES Assignment(id) ON DELETE CASCADE,
    FOREIGN KEY (file_id) REFERENCES File(id) ON DELETE CASCADE
);

-- Création de la table assignmentInstructionFile (relation entre devoirs et fichiers)
CREATE TABLE AssignmentInstructionFile (
    assignment_id CHAR(36) NOT NULL,
    file_id CHAR(36) NOT NULL,
    PRIMARY KEY (assignment_id, file_id),
    FOREIGN KEY (assignment_id) REFERENCES Assignment(id) ON DELETE CASCADE,
    FOREIGN KEY (file_id) REFERENCES File(id) ON DELETE CASCADE
);
