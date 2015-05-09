<?php

include_once (__DIR__."/../../config.php");
include_once (__DIR__."/../../session.php");

function connectToDatabase()
{
    // DB connection info
    global $config_db_host;
    global $config_db_name;
    global $config_db_user;
    global $config_db_pwd;
    try{
        $conn = new PDO("mysql:host=$config_db_host;dbname=$config_db_name", $config_db_user, $config_db_pwd, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //$conn = null; siin peaks toimuma andmebaasi kinnipanemine
    }
    catch(Exception $e){
        die(print_r($e));
    }
    return $conn;
}
/*
function markItemComplete($item_id)
{
    $conn = connect();
    $sql = "UPDATE items SET is_complete = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(1, $item_id);
    $stmt->execute();
}
*/

function getNews()
{
    $conn = connectToDatabase();
    $sql = "SELECT * FROM news ORDER BY datetime DESC";
    $stmt = $conn->query($sql);
    $result = $stmt->fetchAll();
    $conn = NULL;
    return $result;
}

function addNews($user, $title, $content, $date)
{
    $conn = connectToDatabase();
    $sql = "INSERT INTO news (user, title, content, datetime) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(1, $user);
    $stmt->bindValue(2, $title);
    $stmt->bindValue(3, $content);
    $stmt->bindValue(4, $date);
    $stmt->execute();
    $conn = NULL;
}

function updateNews($id, $user, $title, $content, $date)
{
    $conn = connectToDatabase();
    $sql = "UPDATE news SET news.user=?, title=?, content=?, datetime=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(1, $user);
    $stmt->bindValue(2, $title);
    $stmt->bindValue(3, $content);
    $stmt->bindValue(4, $date);
    $stmt->bindValue(5, $id);
    $stmt->execute();
    $conn = NULL;
}

function deleteNews($news_id)
{
    $conn = connectToDatabase();
    $sql = "DELETE FROM news WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(1, $news_id);
    $stmt->execute();
    $conn = NULL;
}

function getUsersAndNews($start_row, $nbr_of_rows)
{
    $conn = connectToDatabase();
    $sql = "select users.mail, users.firstname, users.lastname, news.id, news.title, news.content, news.datetime
            from news inner join users on news.user = users.mail
            order by news.datetime DESC LIMIT $start_row, $nbr_of_rows";
    $stmt = $conn->query($sql);
    $result = $stmt->fetchAll();
    $conn = NULL;
    return $result;
}

function getUsersAndNewsById($id)
{
    $conn = connectToDatabase();
    $sql = "SELECT users.mail, users.firstname, users.lastname, news.id, news.title, news.content
            FROM news INNER JOIN users ON news.user = users.mail
            WHERE news.id = $id";
    $stmt = $conn->query($sql);
    $result = $stmt->fetch();
    $conn = NULL;
    return $result;
}

function getUsers()
{
    $conn = connectToDatabase();
    $sql = "SELECT * FROM users";
    $stmt = $conn->query($sql);
    $result = $stmt->fetchAll();
    $conn = NULL;
    return $result;
}

function getNbrOfNewsByUsers()
{
    $conn = connectToDatabase();
    $sql = "select users.mail, users.firstname, users.lastname, count(news.title) as arv from news inner join users on news.user = users.mail GROUP BY users.mail";
    $stmt = $conn->query($sql);
    $result = $stmt->fetchAll();
    $conn = NULL;
    return $result;
}

function getTotalNbrOfNews()
{
    $conn = connectToDatabase();
    $sql = "SELECT COUNT(*) arv FROM news";
    $stmt = $conn->query($sql);
    $result = $stmt->fetch()["arv"];
    $conn = NULL;
    return $result;
}

function getUsersAndNewsAfterDate($datetime)
{
    $conn = connectToDatabase();
    $sql = "SELECT users.mail, users.firstname, users.lastname, news.id, news.title, news.content, news.datetime
            FROM news INNER JOIN users ON news.user = users.mail
            WHERE news.datetime > '$datetime'
            ORDER BY news.datetime DESC";
    $stmt = $conn->query($sql);
    $result = $stmt->fetchAll();
    $conn = NULL;
    return $result;
}

function getNewsHtml($messages) {
    $data = "";
    foreach($messages as $message) {
        $data .= "
            <div class='news-story' id='news_{$message["id"]}'>
                <h3 class='news-title'>{$message["title"]}</h3>
                <p class='news-author'>Autor: {$message["firstname"]} {$message["lastname"]}</p>
                <p class='news-content'>{$message["content"]}</p>
                ";
            if (isUserLoggedIn()) {
                $data .= "
                <p class='news-mod-link'>
                    <a href='mysql-tasklist/news/deleteNewsFromDB.php?id={$message["id"]}'>Kustuta</a>
                    <span> | </span>
                    <a href='#edit_{$message["id"]}' class='edit_news_button'>Muuda</a>
                </p>";
            }
        $data .= "
            </div>
            ";
    }
    return $data;
}

function getBlognames()
{
    $conn = connectToDatabase();
    $sql = "SELECT blogname, username FROM blog ORDER BY blogname";
    $stmt = $conn->query($sql);
    $result = $stmt->fetchAll();
    $conn = NULL;
    return $result;
}

function getBlognamesByUser($user)
{
    $conn = connectToDatabase();
    $user = $conn->quote($user);
    $sql = "SELECT blogname FROM blog WHERE username = $user ORDER BY blogname";
    $stmt = $conn->query($sql);
    $result = $stmt->fetchAll();
    $conn = NULL;
    return $result;
}

function addBlog($user, $name)
{
    $conn = connectToDatabase();
    $sql = "INSERT INTO blog (username, blogname) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(1, $user);
    $stmt->bindValue(2, $name);
    $stmt->execute();
    $conn = NULL;
}

function addBlogEntry($name, $date, $content)
{
    $conn = connectToDatabase();
    $sql = "INSERT INTO blogentry (blogname, blogdate, blogcontent) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(1, $name);
    $stmt->bindValue(2, $date);
    $stmt->bindValue(3, $content);
    $stmt->execute();
    $conn = NULL;
}

function getBlogEntriesByName($name)
{
    $conn = connectToDatabase();
    $name = $conn->quote($name);
    $sql = "SELECT idblogentry, blogname, blogdate, blogcontent FROM blogentry WHERE blogname = $name ORDER BY blogdate DESC";
    $stmt = $conn->query($sql);
    $result = $stmt->fetchAll();
    $conn = NULL;
    return $result;
}

function getBlogEntryHtml($blogentries) {
    $data = "";
    foreach($blogentries as $blogentry) {
        $data .= "
            <div class='blog-entry' id='blog_{$blogentry["idblogentry"]}'>
                <p class='blog-entry-date'>Kuup&#228ev: {$blogentry["blogdate"]}</p>
                <p class='blog-entry-content'>{$blogentry["blogcontent"]}</p>
            </div>";
    }
    return $data;
}

?>

