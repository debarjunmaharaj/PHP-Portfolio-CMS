--
-- Database: `futurev1_demo`
--

CREATE DATABASE IF NOT EXISTS `futurev1_demo` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `futurev1_demo`;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admin`
--
-- The password is "password". Always use a strong, unique password in production.
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$wSgH2r.r6CUP87b5Kx2Z6eP3XzN5yF/3f2.ax2KzGk7yX.neG5vWi');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `title`, `description`, `image`, `link`, `sort_order`) VALUES
(1, 'E-commerce Platform', 'A full-featured online store with a custom CMS and payment gateway integration.', 'project1.jpg', '#', 0),
(2, 'Corporate Website Redesign', 'Modern and responsive redesign for a major tech company, focusing on UX and performance.', 'project2.jpg', '#', 1),
(3, 'Mobile Banking App', 'A secure and intuitive mobile app for iOS and Android, built with React Native.', 'project3.jpg', '#', 2);


-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('about_me', 'I am a passionate developer with a love for creating clean, efficient, and beautiful web applications. With a strong foundation in both front-end and back-end technologies, I enjoy bringing ideas to life in the browser.'),
('contact_email', 'your-email@example.com'),
('footer_text', '© 2024 Your Name. All Rights Reserved.'),
('hero_subtitle', 'Full-Stack Web Developer & UI/UX Enthusiast'),
('hero_title', 'John Doe'),
('logo', 'logo.png'),
('site_title', 'My Portfolio');

-- --------------------------------------------------------

--
-- Table structure for table `slider_images`
--

CREATE TABLE `slider_images` (
  `id` int(11) NOT NULL,
  `image_name` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `slider_images`
--

INSERT INTO `slider_images` (`id`, `image_name`, `sort_order`) VALUES
(1, 'slide1.jpg', 0),
(2, 'slide2.jpg', 1),
(3, 'slide3.jpg', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `slider_images`
--
ALTER TABLE `slider_images`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `slider_images`
--
ALTER TABLE `slider_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;