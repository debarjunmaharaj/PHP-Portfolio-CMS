<?php
// ====================================================================================
// CONFIGURATION & INITIALIZATION (No changes needed here)
// ====================================================================================
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- DATABASE CONNECTION ---
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'futurev1_demo');
define('DB_PASSWORD', 'futurev1_demo');
define('DB_NAME', 'futurev1_demo');

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- GLOBAL VARIABLES & HELPERS ---
$upload_dir = 'uploads/';
$settings = [];
$slider_images = [];
$projects = [];
$page_title = 'My Portfolio';

function get_setting($key, $default = '') {
    global $settings;
    return isset($settings[$key]) ? htmlspecialchars($settings[$key]) : htmlspecialchars($default);
}

$result = $conn->query("SELECT * FROM settings");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    $page_title = get_setting('site_title', 'My Portfolio');
}

// ====================================================================================
// ROUTING & PAGE LOGIC (No changes needed here)
// ====================================================================================
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// ====================================================================================
// ADMIN LOGIC (No changes needed here)
// ====================================================================================
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_general'])) {
        $fields_to_update = ['site_title', 'hero_title', 'hero_subtitle', 'about_me', 'contact_email', 'footer_text'];
        $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        foreach ($fields_to_update as $field) {
            if (isset($_POST[$field])) {
                $stmt->bind_param("ss", $_POST[$field], $field);
                $stmt->execute();
            }
        }
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $logo_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $logo_filename = 'logo.' . $logo_extension;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $logo_filename)) {
                $key = 'logo';
                $stmt->bind_param("ss", $logo_filename, $key);
                $stmt->execute();
            }
        }
        $stmt->close();
        header("Location: index.php?page=admin&message=" . urlencode("General settings updated!"));
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_slider_image'])) {
        if (isset($_FILES['slider_image']) && $_FILES['slider_image']['error'] == 0) {
            $filename = time() . '_' . basename($_FILES['slider_image']['name']);
            if (move_uploaded_file($_FILES['slider_image']['tmp_name'], $upload_dir . $filename)) {
                $stmt = $conn->prepare("INSERT INTO slider_images (image_name) VALUES (?)");
                $stmt->bind_param("s", $filename);
                $stmt->execute();
                $stmt->close();
            }
        }
        header("Location: index.php?page=admin&message=" . urlencode("Slider image added!"));
        exit;
    }
    if (isset($_GET['delete_slider'])) {
        $id_to_delete = intval($_GET['delete_slider']);
        $stmt = $conn->prepare("SELECT image_name FROM slider_images WHERE id = ?");
        $stmt->bind_param("i", $id_to_delete);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) { @unlink($upload_dir . $row['image_name']); }
        $stmt->close();
        $stmt = $conn->prepare("DELETE FROM slider_images WHERE id = ?");
        $stmt->bind_param("i", $id_to_delete);
        $stmt->execute();
        $stmt->close();
        header("Location: index.php?page=admin&message=" . urlencode("Slider image deleted!"));
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_project'])) {
        $id = intval($_POST['project_id']);
        $title = $_POST['title'];
        $description = $_POST['description'];
        $link = $_POST['link'];
        $image = $_POST['current_image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $filename = time() . '_' . basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                if (!empty($image)) { @unlink($upload_dir . $image); }
                $image = $filename;
            }
        }
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE projects SET title = ?, description = ?, link = ?, image = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $title, $description, $link, $image, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO projects (title, description, link, image) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $title, $description, $link, $image);
        }
        $stmt->execute();
        $stmt->close();
        header("Location: index.php?page=admin&message=" . urlencode("Project saved!"));
        exit;
    }
    if (isset($_GET['delete_project'])) {
        $id_to_delete = intval($_GET['delete_project']);
        $stmt = $conn->prepare("SELECT image FROM projects WHERE id = ?");
        $stmt->bind_param("i", $id_to_delete);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) { @unlink($upload_dir . $row['image']); }
        $stmt->close();
        $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->bind_param("i", $id_to_delete);
        $stmt->execute();
        $stmt->close();
        header("Location: index.php?page=admin&message=" . urlencode("Project deleted!"));
        exit;
    }
}
if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT id, password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            header("Location: index.php?page=admin");
            exit;
        }
    }
    $login_error = "Invalid username or password.";
}
if ($page === 'logout') {
    session_destroy();
    header("Location: index.php?page=login");
    exit;
}
// ====================================================================================
// DATA FETCHING (No changes needed here)
// ====================================================================================
$result = $conn->query("SELECT * FROM slider_images ORDER BY sort_order ASC");
if($result) { while($row = $result->fetch_assoc()) { $slider_images[] = $row; } }
$result = $conn->query("SELECT * FROM projects ORDER BY sort_order ASC, id DESC");
if($result) { while($row = $result->fetch_assoc()) { $projects[] = $row; } }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <!-- ADDING GOOGLE FONT -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- ENHANCED STYLES START HERE -->
    <style>
        :root {
            --primary-glow: #00c6ff;
            --secondary-glow: #0072ff;
            --primary-accent: #f857a6;
            --secondary-accent: #ff5858;
            --bg-dark-1: #121212;
            --bg-dark-2: #1e1e1e;
            --text-light: #e0e0e0;
            --text-dark: #333;
            --glass-bg: rgba(22, 22, 22, 0.6);
        }
        /* --- GENERAL & ANIMATIONS --- */
        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background: var(--bg-dark-1);
            color: var(--text-light);
            line-height: 1.7;
            overflow-x: hidden;
        }
        .container { max-width: 1100px; margin: auto; padding: 0 20px; }
        section { padding: 80px 0; }
        .section-title {
            text-align: center;
            margin-bottom: 50px;
            font-size: 2.8rem;
            font-weight: 700;
            position: relative;
        }
        .section-title::after {
            content: '';
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-accent), var(--secondary-accent));
            border-radius: 2px;
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
        }
        /* --- SCROLL-TRIGGERED ANIMATIONS --- */
        .animate-on-scroll {
            opacity: 0;
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }
        .animate-on-scroll.fade-in { transform: translateY(30px); }
        .animate-on-scroll.is-visible { opacity: 1; transform: translateY(0); }
        
        /* --- HEADER (GLASSMORPHISM) --- */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.4s ease-in-out;
        }
        .navbar.scrolled {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }
        .navbar .logo img { height: 45px; transition: transform 0.3s ease; }
        .navbar .logo img:hover { transform: scale(1.1); }
        .navbar .nav-links { list-style: none; display: flex; gap: 25px; margin: 0; padding: 0; }
        .navbar .nav-links a {
            text-decoration: none;
            color: var(--text-light);
            font-weight: 500;
            transition: color 0.3s ease, text-shadow 0.3s ease;
        }
        .navbar .nav-links a:hover {
            color: #fff;
            text-shadow: 0 0 10px var(--primary-glow);
        }

        /* --- HERO SECTION (GRADIENT TEXT & ANIMATION) --- */
        .hero {
            position: relative;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            overflow: hidden;
        }
        .hero-slider { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -2; }
        .slide {
            position: absolute; width: 100%; height: 100%;
            background-size: cover; background-position: center;
            opacity: 0; transition: opacity 2s ease-in-out; animation: zoomEffect 20s infinite;
        }
        .slide.active { opacity: 1; }
        @keyframes zoomEffect { 0% { transform: scale(1); } 50% { transform: scale(1.1); } 100% { transform: scale(1); } }
        .hero::before {
            content: ''; position: absolute; top: 0; left: 0;
            width: 100%; height: 100%; background: rgba(18, 18, 18, 0.7); z-index: -1;
        }
        .hero-content { opacity: 0; animation: fadeIn 1s 0.5s forwards; }
        @keyframes fadeIn { to { opacity: 1; } }
        .hero h1 {
            font-size: 4.5rem; margin-bottom: 0.5rem; font-weight: 700;
            background: linear-gradient(90deg, var(--primary-glow), var(--secondary-glow), var(--primary-glow));
            background-size: 200% auto;
            color: #fff;
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradient-flow 4s linear infinite;
        }
        @keyframes gradient-flow { to { background-position: 200% center; } }
        .hero p { font-size: 1.5rem; font-weight: 300; }

        /* --- ABOUT SECTION --- */
        #about p { max-width: 800px; margin: auto; text-align: center; font-size: 1.1rem; }

        /* --- PORTFOLIO (3D TILT CARDS) --- */
        #portfolio { background: var(--bg-dark-2); }
        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 40px;
            /* Add perspective for 3D effect */
            perspective: 1000px;
        }
        .project-card {
            background: var(--bg-dark-1);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
            position: relative;
            /* This is key for the 3D effect */
            transform-style: preserve-3d;
        }
        .project-card:hover {
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }
        .project-card img {
            width: 100%; height: 220px;
            object-fit: cover; display: block;
            transition: transform 0.4s ease;
        }
        .project-card:hover img { transform: translateZ(20px); }
        .project-content { padding: 25px; }
        .project-content h3 { margin-top: 0; font-size: 1.5rem; }
        .project-content p { font-size: 0.95rem; color: #b0b0b0; }
        .project-content .project-link {
            display: inline-block; margin-top: 15px; text-decoration: none;
            color: var(--primary-glow); font-weight: 600;
            transition: letter-spacing 0.3s ease, color 0.3s ease;
        }
        .project-content .project-link:hover {
            letter-spacing: 1px;
            color: #fff;
        }

        /* --- CONTACT --- */
        #contact-form { max-width: 700px; margin: auto; }
        .form-group { margin-bottom: 20px; }
        .form-group input, .form-group textarea {
            width: 100%; padding: 15px;
            border-radius: 8px; border: 1px solid #444;
            background-color: var(--bg-dark-2); color: var(--text-light);
            font-size: 1rem; font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none; border-color: var(--primary-glow);
            box-shadow: 0 0 15px rgba(0, 198, 255, 0.3);
        }
        .form-group .btn-submit {
            display: block; width: 100%; padding: 15px; border: none;
            background: linear-gradient(90deg, var(--primary-accent), var(--secondary-accent));
            color: white; font-size: 1.1rem; font-weight: 600;
            border-radius: 8px; cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .form-group .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 88, 88, 0.2);
        }

        /* --- FOOTER --- */
        footer {
            background: var(--bg-dark-2);
            text-align: center;
            padding: 30px 0;
            border-top: 1px solid #333;
        }
        footer p { margin: 0; color: #888; }
        
        /* Admin Panel styles remain the same for functionality */
        .admin-wrapper{display:flex}.admin-sidebar{width:250px;background:#343a40;color:#fff;min-height:100vh;padding:20px}.admin-sidebar h2{color:#fff;text-align:center}.admin-sidebar ul{list-style:none;padding:0}.admin-sidebar ul li a{display:block;padding:10px 15px;color:#f8f9fa;text-decoration:none;border-radius:5px;margin-bottom:5px;transition:background .3s ease}.admin-sidebar ul li a:hover,.admin-sidebar ul li a.active{background:#007bff}.admin-content{flex-grow:1;padding:40px;background:#fff;color:var(--text-dark)}.admin-content h1{border-bottom:2px solid #eee;padding-bottom:10px}.admin-form-section{background:#f8f9fa;padding:20px;border-radius:8px;margin-bottom:30px}.admin-form-section h2{margin-top:0}.admin-form-section .form-group{margin-bottom:15px}.admin-form-section label{display:block;margin-bottom:5px;font-weight:700}.admin-form-section input[type=text],.admin-form-section input[type=email],.admin-form-section textarea{width:100%;padding:8px;box-sizing:border-box;border:1px solid #ccc;border-radius:4px}.admin-form-section button{background:#007bff;color:#fff;padding:10px 20px;border:none;border-radius:4px;cursor:pointer}.admin-form-section button:hover{background:#0056b3}.current-img-preview{max-width:100px;margin-top:10px;display:block}.admin-table{width:100%;border-collapse:collapse;margin-top:20px}.admin-table th,.admin-table td{border:1px solid #ddd;padding:8px;text-align:left;vertical-align:middle}.admin-table th{background:#f2f2f2}.admin-table img{max-width:80px}.admin-table .actions a{margin-right:10px;text-decoration:none}.admin-table .actions .edit{color:#28a745}.admin-table .actions .delete{color:#dc3545}.admin-message{padding:15px;margin-bottom:20px;border-radius:5px;background:#d4edda;color:#155724;border:1px solid #c3e6cb}.login-container{display:flex;align-items:center;justify-content:center;height:100vh;background:#f8f9fa}.login-box{background:#fff;padding:40px;border-radius:8px;box-shadow:0 0 20px rgba(0,0,0,.1);width:350px;text-align:center;color:var(--text-dark)}.login-box h1{margin-bottom:20px}.login-error{color:#dc3545;margin-bottom:15px}
    </style>
</head>
<body>

<?php
// ====================================================================================
// PAGE RENDERING SWITCH (Admin/Login parts are unchanged)
// ====================================================================================
switch ($page):
    case 'login':
        ?>
        <div class="login-container">
            <div class="login-box">
                <h1>Admin Login</h1>
                <?php if (isset($login_error)): ?><p class="login-error"><?php echo $login_error; ?></p><?php endif; ?>
                <form action="index.php?page=login" method="post">
                    <div class="form-group"><input type="text" name="username" placeholder="Username" required></div>
                    <div class="form-group"><input type="password" name="password" placeholder="Password" required></div>
                    <div class="form-group"><button type="submit" class="btn-submit">Login</button></div>
                </form>
            </div>
        </div>
        <?php
        break;

    case 'admin':
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) { header("Location: index.php?page=login"); exit; }
        $admin_projects = []; $result = $conn->query("SELECT * FROM projects ORDER BY sort_order ASC, id DESC"); while($row = $result->fetch_assoc()) $admin_projects[] = $row;
        $admin_sliders = []; $result = $conn->query("SELECT * FROM slider_images ORDER BY sort_order ASC, id DESC"); while($row = $result->fetch_assoc()) $admin_sliders[] = $row;
        $edit_project_data = null;
        if(isset($_GET['edit_project'])) {
            $stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?"); $stmt->bind_param("i", $_GET['edit_project']); $stmt->execute();
            $edit_project_data = $stmt->get_result()->fetch_assoc(); $stmt->close();
        }
        ?>
        <div class="admin-wrapper">
            <aside class="admin-sidebar">
                <h2>Admin Panel</h2>
                <ul>
                    <li><a href="index.php?page=admin" class="active">Dashboard</a></li>
                    <li><a href="index.php" target="_blank">View Site</a></li>
                    <li><a href="index.php?page=logout">Logout</a></li>
                </ul>
            </aside>
            <main class="admin-content">
                <h1>Dashboard</h1>
                <?php if (isset($_GET['message'])): ?><div class="admin-message"><?php echo htmlspecialchars($_GET['message']); ?></div><?php endif; ?>
                <div class="admin-form-section"><h2>General Settings</h2><form action="index.php?page=admin" method="post" enctype="multipart/form-data"><input type="hidden" name="update_general" value="1"><div class="form-group"><label>Site Title</label><input type="text" name="site_title" value="<?php echo get_setting('site_title'); ?>"></div><div class="form-group"><label>Logo</label><input type="file" name="logo"><br><small>Current: <?php echo get_setting('logo'); ?></small><?php if(get_setting('logo')): ?><img src="<?php echo $upload_dir.get_setting('logo'); ?>" class="current-img-preview"><?php endif; ?></div><div class="form-group"><label>Hero Title</label><input type="text" name="hero_title" value="<?php echo get_setting('hero_title'); ?>"></div><div class="form-group"><label>Hero Subtitle</label><input type="text" name="hero_subtitle" value="<?php echo get_setting('hero_subtitle'); ?>"></div><div class="form-group"><label>About Me Text</label><textarea name="about_me" rows="5"><?php echo get_setting('about_me'); ?></textarea></div><div class="form-group"><label>Contact Email</label><input type="email" name="contact_email" value="<?php echo get_setting('contact_email'); ?>"></div><div class="form-group"><label>Footer Text</label><input type="text" name="footer_text" value="<?php echo get_setting('footer_text'); ?>"></div><button type="submit">Save Settings</button></form></div>
                <div class="admin-form-section"><h2>Background Slider</h2><form action="index.php?page=admin" method="post" enctype="multipart/form-data"><input type="hidden" name="add_slider_image" value="1"><div class="form-group"><label>Add New Image</label><input type="file" name="slider_image" required></div><button type="submit">Upload</button></form><table class="admin-table"><thead><tr><th>Preview</th><th>Filename</th><th>Actions</th></tr></thead><tbody><?php foreach($admin_sliders as $s):?><tr><td><img src="<?php echo $upload_dir.htmlspecialchars($s['image_name']);?>"></td><td><?php echo htmlspecialchars($s['image_name']);?></td><td class="actions"><a href="index.php?page=admin&delete_slider=<?php echo $s['id'];?>" class="delete" onclick="return confirm('Sure?')">Delete</a></td></tr><?php endforeach;?></tbody></table></div>
                <div class="admin-form-section"><h2><?php echo $edit_project_data?'Edit':'Add New';?> Project</h2><form action="index.php?page=admin" method="post" enctype="multipart/form-data"><input type="hidden" name="save_project" value="1"><input type="hidden" name="project_id" value="<?php echo $edit_project_data['id']??'0';?>"><input type="hidden" name="current_image" value="<?php echo $edit_project_data['image']??'';?>"><div class="form-group"><label>Title</label><input type="text" name="title" value="<?php echo htmlspecialchars($edit_project_data['title']??'');?>" required></div><div class="form-group"><label>Description</label><textarea name="description" rows="4" required><?php echo htmlspecialchars($edit_project_data['description']??'');?></textarea></div><div class="form-group"><label>Link</label><input type="text" name="link" value="<?php echo htmlspecialchars($edit_project_data['link']??'#');?>"></div><div class="form-group"><label>Image</label><input type="file" name="image"<?php echo !$edit_project_data?' required':'';?>><?php if(!empty($edit_project_data['image'])):?><br><small>Current:</small><br><img src="<?php echo $upload_dir.htmlspecialchars($edit_project_data['image']);?>" class="current-img-preview"><?php endif;?></div><button type="submit">Save</button><?php if($edit_project_data):?> <a href="index.php?page=admin" style="margin-left:10px;text-decoration:none;">Cancel</a><?php endif;?></form><table class="admin-table"><thead><tr><th>Image</th><th>Title</th><th>Actions</th></tr></thead><tbody><?php foreach($admin_projects as $p):?><tr><td><img src="<?php echo $upload_dir.htmlspecialchars($p['image']);?>"></td><td><?php echo htmlspecialchars($p['title']);?></td><td class="actions"><a href="index.php?page=admin&edit_project=<?php echo $p['id'];?>" class="edit">Edit</a> <a href="index.php?page=admin&delete_project=<?php echo $p['id'];?>" class="delete" onclick="return confirm('Sure?')">Delete</a></td></tr><?php endforeach;?></tbody></table></div>
            </main>
        </div>
        <?php
        break;

    default:
        // --- NEW ENHANCED HOME PAGE (PUBLIC PORTFOLIO) ---
        ?>
        <header class="navbar" id="navbar">
            <div class="container">
                <a href="#hero" class="logo">
                    <img src="<?php echo $upload_dir . get_setting('logo'); ?>" alt="Logo">
                </a>
                <nav>
                    <ul class="nav-links">
                        <li><a href="#about">About</a></li>
                        <li><a href="#portfolio">Portfolio</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </header>

        <section class="hero" id="hero">
            <div class="hero-slider">
                <?php foreach($slider_images as $index => $slide): ?>
                <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>" style="background-image: url('<?php echo $upload_dir . htmlspecialchars($slide['image_name']); ?>');"></div>
                <?php endforeach; ?>
            </div>
            <div class="hero-content">
                <h1><?php echo get_setting('hero_title', 'Your Name'); ?></h1>
                <p><?php echo get_setting('hero_subtitle', 'Web Developer & Designer'); ?></p>
            </div>
        </section>

        <div id="about" class="animate-on-scroll fade-in">
            <section>
                <div class="container">
                    <h2 class="section-title animate-on-scroll fade-in">About Me</h2>
                    <p class="animate-on-scroll fade-in"><?php echo nl2br(get_setting('about_me')); ?></p>
                </div>
            </section>
        </div>

        <div id="portfolio" class="animate-on-scroll fade-in">
            <section>
                <div class="container">
                    <h2 class="section-title animate-on-scroll fade-in">My Work</h2>
                    <div class="portfolio-grid">
                        <?php foreach($projects as $project): ?>
                        <div class="project-card animate-on-scroll fade-in">
                            <img src="<?php echo $upload_dir . htmlspecialchars($project['image']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>">
                            <div class="project-content">
                                <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                                <p><?php echo htmlspecialchars($project['description']); ?></p>
                                <a href="<?php echo htmlspecialchars($project['link']); ?>" target="_blank" class="project-link">View Project →</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </div>
        
        <div id="contact" class="animate-on-scroll fade-in">
            <section>
                <div class="container">
                    <h2 class="section-title animate-on-scroll fade-in">Get In Touch</h2>
                    <p style="text-align:center; margin-bottom: 30px;" class="animate-on-scroll fade-in">Have a question or want to work together? My inbox is always open.</p>
                    <form id="contact-form" action="mailto:<?php echo get_setting('contact_email'); ?>" method="post" enctype="text/plain" class="animate-on-scroll fade-in">
                        <div class="form-group"><input type="text" name="name" placeholder="Your Name" required></div>
                        <div class="form-group"><input type="email" name="email" placeholder="Your Email" required></div>
                        <div class="form-group"><textarea name="message" rows="6" placeholder="Your Message" required></textarea></div>
                        <div class="form-group"><button type="submit" class="btn-submit">Send Message</button></div>
                    </form>
                </div>
            </section>
        </div>

        <footer>
            <div class="container">
                <p><?php echo get_setting('footer_text', '© 2024. All rights reserved.'); ?></p>
            </div>
        </footer>

        <!-- NEW & ENHANCED JAVASCRIPT -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Navbar scroll effect
            const navbar = document.getElementById('navbar');
            if(navbar) {
                window.addEventListener('scroll', () => {
                    if (window.scrollY > 50) {
                        navbar.classList.add('scrolled');
                    } else {
                        navbar.classList.remove('scrolled');
                    }
                });
            }

            // Hero background slider
            const slides = document.querySelectorAll('.hero-slider .slide');
            if (slides.length > 1) {
                let currentSlide = 0;
                setInterval(() => {
                    slides[currentSlide].classList.remove('active');
                    currentSlide = (currentSlide + 1) % slides.length;
                    slides[currentSlide].classList.add('active');
                }, 5000);
            }

            // 3D Tilt effect for project cards
            const cards = document.querySelectorAll('.project-card');
            cards.forEach(card => {
                card.addEventListener('mousemove', e => {
                    const rect = card.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    const { width, height } = rect;
                    const rotateX = (y - height / 2) / (height / 2) * -7;
                    const rotateY = (x - width / 2) / (width / 2) * 7;
                    card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.05)`;
                });
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale(1)';
                });
            });

            // Scroll-triggered animations using Intersection Observer
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                    }
                });
            }, {
                threshold: 0.1 // Trigger when 10% of the element is visible
            });

            const elementsToAnimate = document.querySelectorAll('.animate-on-scroll');
            elementsToAnimate.forEach(el => observer.observe(el));
        });
        </script>
        <?php
        break;
endswitch;
$conn->close();
?>
</body>
</html>