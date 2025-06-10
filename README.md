# Dynamic Animated PHP Portfolio with Admin Panel

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

A complete, single-file portfolio website built with PHP and MySQL. It features a dynamic front-end with animations and a secure, session-based admin panel for managing all site content without touching the code. This project is designed as an excellent educational tool for understanding how front-end, back-end, and database components work together in a single script.


*(Note: You can replace the image link above with a screenshot of your own finished site.)*

## ✨ Key Features

*   **Dynamic Content:** All text, images, and links are fetched from a MySQL database.
*   **Full Admin Panel:** Log in to a secure backend to manage every aspect of the site.
*   **Content Management:**
    *   Update site title, logo, and footer text.
    *   Change the hero section's title and subtitle.
    *   Manage the animated background slider images (add/delete).
    *   Add, edit, and delete portfolio projects (CRUD).
*   **WhatsApp Contact Form:** A modern contact form that opens a pre-filled WhatsApp chat window.
*   **Animated & Responsive:** Smooth CSS animations and a mobile-friendly layout.
*   **Single-File Architecture:** The entire application logic is contained within `index.php` for easy learning and setup.

## 🚀 Setup and Deployment

Follow these steps to get the project running on your own server (e.g., using XAMPP, WAMP, or a live web host).

### Prerequisites

*   A web server with PHP support (e.g., Apache).
*   A MySQL or MariaDB database server.
*   A tool to manage the database, like phpMyAdmin (recommended).

### Step 1: Get the Code

Clone this repository or download the ZIP file and place it in your web server's root directory (like `htdocs` in XAMPP).

```bash
git clone https://github.com/your-username/your-repo-name.git
```

### Step 2: Database Setup

1.  Open phpMyAdmin.
2.  Create a new database. You can name it `portfolio_db` or use the name from your hosting provider (e.g., `futurev1_demo`).
3.  Select the new database you just created.
4.  Click on the **"Import"** tab at the top.
5.  Click "Choose File" and select the `database.sql` file included in this project.
6.  Click the **"Go"** button at the bottom of the page. This will create all the necessary tables and populate them with default content.

### Step 3: Configure the Connection

1.  Open the `index.php` file in a code editor.
2.  Go to the top of the file and find the database connection block.
3.  **Change the following four lines** to match your database credentials:

    ```php
    // --- DATABASE CONNECTION ---
    // IMPORTANT: Change these details to match your database configuration
    define('DB_SERVER', 'localhost'); // Usually 'localhost'
    define('DB_USERNAME', 'root'); // Your database username
    define('DB_PASSWORD', ''); // Your database password
    define('DB_NAME', 'portfolio_db'); // The name of the database you created
    ```

### Step 4: Folder Permissions

You must give your web server permission to save images.

1.  In your project folder, create a new folder named `uploads`.
2.  Right-click this folder and set its permissions to be writable by the server (often permissions `755` or `777`, depending on your server configuration).

### Step 5: Run the Site

Navigate to your project's URL in your web browser.
*   **Local Server:** `http://localhost/your-project-folder-name/`
*   **Live Server:** `http://www.yourdomain.com/`

## ⚙️ How to Use

### Admin Panel

The admin panel is where you control all the site's content.

1.  **Access the Login Page:**
    Go to `http://your-site-url/index.php?page=login`

2.  **Default Login Credentials:**
    *   **Username:** `admin`
    *   **Password:** `password`

3.  **What You Can Do:**
    *   **General Settings:** Change the site title, logo, hero text, "About Me" paragraph, footer, and your WhatsApp number.
    *   **Background Slider:** Upload new images for the hero section's background or delete existing ones.
    *   **Projects:** Add new portfolio projects with a title, description, image, and link. You can also edit or delete any existing project.

### Public Site

The public-facing site (`index.php`) will automatically display all the content you have configured in the admin panel. Any changes you save in the admin panel will appear on the live site immediately after you refresh the page.

## ⚠️ Important Note on Project Structure

This project intentionally uses a **single-file structure** to make it easy to understand the relationship between PHP logic, HTML markup, CSS, and JavaScript.

**For a professional, large-scale application, you should always separate concerns** into different files and folders (e.g., using an MVC pattern, having separate CSS/JS files, a dedicated `/admin` folder, a `config.php` file, etc.). This project serves as a foundational learning tool, not a template for complex production systems.

## 📄 License

This project is Made By ![Debarjun Chakraborty](https://www.facebook.com/Debarjunmaharaj).
