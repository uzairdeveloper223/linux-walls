# 🐧 Linux Wallpapers

A community-driven wallpaper sharing platform for Linux enthusiasts. Upload, share, and discover static and live wallpapers for your desktop.

**Live Site:** [wallpapers.likesyou.org](https://wallpapers.likesyou.org)

## Features

- **Static & Live Wallpapers** - Support for images (JPG, PNG, WebP, GIF) and videos (MP4, WebM)
- **Chunked Uploads** - Large files (>9MB) are split into chunks to bypass hosting limits, then reassembled and uploaded to Cloudinary
- **Image Compression** - Client-side compression for large images before upload
- **User Accounts** - Register/login with Discord ID
- **Like & Download** - Track popularity and download counts
- **Shareable Links** - Unique URLs for each wallpaper
- **Responsive Design** - Works on desktop and mobile

## Tech Stack

- **Backend:** PHP 7.4+
- **Database:** MySQL
- **Storage:** Cloudinary (images & videos)
- **Hosting:** InfinityFree
- **Frontend:** Vanilla JS, CSS3

## Project Structure

```
htdocs/
├── api/
│   ├── chunked-upload.php  # Handles large file uploads
│   ├── download.php        # Download counter
│   └── like.php            # Like functionality
├── css/
│   └── style.css           # Main stylesheet
├── includes/
│   ├── header.php          # Site header & nav
│   ├── footer.php          # Site footer
│   └── wallpaper-card.php  # Reusable wallpaper card
├── js/
│   └── main.js             # Client-side functionality
├── config.php              # Database & API config
├── index.php               # Homepage
├── browse.php              # Browse all wallpapers
├── upload.php              # Upload page with chunked upload support
├── share.php               # Individual wallpaper view
├── profile.php             # User profile
├── login.php               # Login page
├── register.php            # Registration page
└── logout.php              # Logout handler
```

## Setup

1. Clone the repo
2. Import the database schema
3. Update `config.php` with your credentials:
   - Database (MySQL)
   - Cloudinary API keys
   - Site URL
4. Upload to your web server

## Database Schema

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    discord_id VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE wallpapers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unique_id VARCHAR(8) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    url TEXT NOT NULL,
    thumbnail_url TEXT,
    cloudinary_public_id VARCHAR(255),
    type ENUM('static', 'live') DEFAULT 'static',
    uploader_id INT,
    likes INT DEFAULT 0,
    downloads INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploader_id) REFERENCES users(id)
);
```

## Chunked Upload System

For files over 8MB, the upload system:
1. Splits the file into 2MB chunks client-side
2. Uploads each chunk to the server
3. Reassembles chunks on the server
4. Uploads the complete file to Cloudinary

This bypasses InfinityFree's 10MB upload limit while supporting files up to 50MB.

## Credits

Created by **Uzair Mughal** (Discord: mughal_x22)

---

*Made with ❤️ for the Linux community*
