-- Database Setup for Linux Wallpapers
-- Run this SQL to create the required tables

CREATE DATABASE IF NOT EXISTS wallpaper_db;
USE wallpaper_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    discord_id VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Wallpapers table
CREATE TABLE IF NOT EXISTS wallpapers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unique_id VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    url VARCHAR(500) NOT NULL,
    thumbnail_url VARCHAR(500),
    cloudinary_public_id VARCHAR(255),
    type ENUM('static', 'live') DEFAULT 'static',
    likes INT DEFAULT 0,
    downloads INT DEFAULT 0,
    uploader_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploader_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Likes tracking (prevent duplicate likes)
CREATE TABLE IF NOT EXISTS wallpaper_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wallpaper_id INT NOT NULL,
    user_ip VARCHAR(45) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (wallpaper_id, user_ip),
    FOREIGN KEY (wallpaper_id) REFERENCES wallpapers(id) ON DELETE CASCADE
);

-- Downloads tracking
CREATE TABLE IF NOT EXISTS wallpaper_downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wallpaper_id INT NOT NULL,
    user_ip VARCHAR(45) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wallpaper_id) REFERENCES wallpapers(id) ON DELETE CASCADE
);

-- Index for faster queries
CREATE INDEX idx_wallpaper_type ON wallpapers(type);
CREATE INDEX idx_wallpaper_likes ON wallpapers(likes DESC);
CREATE INDEX idx_wallpaper_downloads ON wallpapers(downloads DESC);
