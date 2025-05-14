-- USERS
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    email TEXT NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_info TEXT,
    role VARCHAR(255),
    last_login DATETIME,
    created DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- CATEGORIES
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(255),
    parent_category INT,
    FOREIGN KEY (parent_category) REFERENCES categories(category_id)
) ENGINE=InnoDB;

-- ARTICLES
CREATE TABLE articles (
    article_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    content LONGTEXT,
    author INT,
    status VARCHAR(50),
    category_id INT,
    tags TEXT,
    views INT DEFAULT 0,
    likes INT DEFAULT 0,
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author) REFERENCES users(user_id),
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
) ENGINE=InnoDB;

-- COMMENTS
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT,
    name VARCHAR(255),
    email VARCHAR(255),
    comment TEXT,
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_approved INT DEFAULT 0,
    FOREIGN KEY (article_id) REFERENCES articles(article_id)
) ENGINE=InnoDB;

-- MEDIA
CREATE TABLE media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename TEXT,
    path TEXT,
    type VARCHAR(50),
    size INT,
    article_id INT,
    uploaded DATETIME DEFAULT CURRENT_TIMESTAMP,
    uploader VARCHAR(255),
    FOREIGN KEY (article_id) REFERENCES articles(article_id)
) ENGINE=InnoDB;

-- INDEXES (Opsional tapi sangat disarankan untuk performa)
CREATE INDEX idx_articles_status ON articles(status);
CREATE INDEX idx_articles_views ON articles(views);
CREATE INDEX idx_comments_article_id ON comments(article_id);
CREATE INDEX idx_media_article_id ON media(article_id);
