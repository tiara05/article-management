SELECT 
    a.title, 
    a.status, 
    u.username AS author, 
    a.created AS creation_date,
    IFNULL(m.media_count, 0) AS media_count,
    IFNULL(c.comment_count, 0) AS comment_count,
    a.views
FROM 
    articles a
LEFT JOIN 
    users u ON a.author = u.user_id
LEFT JOIN 
    (SELECT article_id, COUNT(*) AS media_count FROM media GROUP BY article_id) m ON a.article_id = m.article_id
LEFT JOIN 
    (SELECT article_id, COUNT(*) AS comment_count FROM comments WHERE is_approved = 1 GROUP BY article_id) c ON a.article_id = c.article_id
ORDER BY 
    a.views DESC;
