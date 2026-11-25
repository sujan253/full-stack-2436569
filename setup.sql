-- Gamestation - Database Setup
-- Run this file once on MI-Linux via phpMyAdmin or mysql command line
-- mysql -u YOUR_USERNAME -p YOUR_DATABASE < setup.sql

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    genre VARCHAR(100) NOT NULL,
    platform VARCHAR(100) NOT NULL,
    developer VARCHAR(255) NOT NULL,
    publisher VARCHAR(255),
    release_year YEAR NOT NULL,
    rating DECIMAL(3,1),
    description TEXT,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Default admin user (password: admin123)
-- Password is hashed with bcrypt - change this after first login!
INSERT INTO users (username, password, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com');

-- Sample game data
INSERT INTO games (title, genre, platform, developer, publisher, release_year, rating, description) VALUES
('The Witcher 3: Wild Hunt', 'RPG', 'PC, PS4, Xbox One', 'CD Projekt Red', 'CD Projekt', 2015, 9.8, 'An open world RPG set in a dark fantasy universe. One of the greatest games ever made.'),
('Elden Ring', 'Action RPG', 'PC, PS5, Xbox Series X', 'FromSoftware', 'Bandai Namco', 2022, 9.5, 'An open world action RPG created by Hidetaka Miyazaki and George R.R. Martin.'),
('Cyberpunk 2077', 'Action RPG', 'PC, PS5, Xbox Series X', 'CD Projekt Red', 'CD Projekt', 2020, 8.5, 'Open world action adventure set in Night City. Had a rough launch but great now.'),
('Hades', 'Roguelike', 'PC, Switch', 'Supergiant Games', 'Supergiant Games', 2020, 9.3, 'A rogue-like dungeon crawler where you fight out of the Underworld. Brilliant story.'),
('God of War', 'Action Adventure', 'PS4, PS5', 'Santa Monica Studio', 'Sony', 2018, 9.6, 'Kratos and Atreus journey through Norse mythology. Stunning game.'),
('Hollow Knight', 'Metroidvania', 'PC, Switch, PS4', 'Team Cherry', 'Team Cherry', 2017, 9.1, 'Explore a vast underground kingdom of insects and heroes. Incredibly polished indie game.'),
('Red Dead Redemption 2', 'Action Adventure', 'PC, PS4, Xbox One', 'Rockstar Games', 'Rockstar', 2018, 9.7, 'Epic tale of life in Americas heartland, 1899. Best open world ever made imo.'),
('Stardew Valley', 'Simulation', 'PC, Switch, PS4, Mobile', 'ConcernedApe', 'ConcernedApe', 2016, 9.0, 'Build a farm, make friends, explore caves. Made by one person. Absolute gem.'),
('Portal 2', 'Puzzle', 'PC, PS3, Xbox 360', 'Valve', 'Valve', 2011, 9.5, 'Mind-bending puzzles with brilliant writing and co-op. Still amazing.'),
('Minecraft', 'Sandbox', 'PC, Console, Mobile', 'Mojang', 'Microsoft', 2011, 9.0, 'Place blocks, build things, survive. The biggest game ever made basically.');
