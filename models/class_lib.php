<?php
class Member {
    protected $member_id;
    protected $username;
    protected $password;
    protected $security_group;
    protected $registration_date;
    protected $email;
    protected $profile_image;
    protected $forename;
    protected $surname;
    protected $profile_description;
    function __construct($member_id, $username, $password, $security_group, $registration_date, $email, $profile_image, $forename, $surname, $profile_description) {
        $this->member_id = $member_id;
        $this->username = $username;
        $this->password = $password;
        $this->security_group = $security_group;
        $this->registration_date = $registration_date;
        $this->email = $email;
        $this->profile_image = $profile_image;
        $this->forename = $forename;
        $this->surname = $surname;
        $this->profile_description = $profile_description;
    }
    function getMember_id() {
        return $this->member_id;
    }
    function getUsername() {
        return $this->username;
    }
    function getPassword() {
        return $this->password;
    }
    function getSecurity_group() {
        return $this->security_group;
    }
    function getRegistration_date() {
        return $this->registration_date;
    }
    function getEmail() {
        return $this->email;
    }
    function getProfile_image() {
        return $this->profile_image;
    }
    function getForename() {
        return $this->forename;
    }
    function getSurname() {
        return $this->surname;
    }
    function getProfile_description() {
        return $this->profile_description;
    }
    function getFavourites($pdo){
        $stmt= $pdo->prepare("SELECT * FROM posts p
                                JOIN favourites f ON f.post_id=p.post_id
                                JOIN members m ON m.member_id=p.member_id
                                WHERE f.member_id=:id");
        $stmt->execute(array(":id" => $_SESSION["id"]));
        $favourites= [];
        while($row= $stmt->fetch(PDO::FETCH_ASSOC)){
            $favourite= new Post($row["post_id"], $row["post_date"],$row["category_id"],$row["member_id"],
                                $row["title"], $row["post_image"],$row["post_content"]);
            array_push($favourites, $favourite);
        }
        return $favourites;
    }
    function getFollowers($pdo){
        $stmt= $pdo->prepare("SELECT m.username FROM members m
                                JOIN follows f ON f.follower_id=m.member_id
                                WHERE f.member_id=:id");
        $stmt->execute(array(":id" => $_SESSION["id"]));
        $followers= [];
        while($row= $stmt->fetch(PDO::FETCH_ASSOC)){
            array_push($followers, $row["username"]);
        }
        return $followers;
    }
    function getFollowed($pdo){
        $stmt= $pdo->prepare("SELECT username FROM members m
                                JOIN follows f ON f.member_id=m.member_id
                                WHERE f.follower_id=:id");
        $stmt->execute(array(":id" => $_SESSION["id"]));
        $followed= [];
        while($row= $stmt->fetch(PDO::FETCH_ASSOC)){
            array_push($followed, $row["username"]);
        }
        return $followed;
    }
    function getOwnPosts($pdo){
        $stmt= $pdo->prepare("SELECT * FROM posts WHERE member_id=:id");
        $stmt->execute(array(":id" => $_SESSION["id"]));
        $own_posts= [];
        while($row= $stmt->fetch(PDO::FETCH_ASSOC)){
            $own_post= new Post($row["post_id"], $row["post_date"],$row["category_id"],$row["member_id"],
                                $row["title"], $row["post_image"],$row["post_content"]);
            array_push($own_posts, $own_post);
        }
        return $own_posts;
    }
    function updateDetails($pdo, $data, $id){
        //checking the password
        $stmt= $pdo->prepare("SELECT username FROM members WHERE member_id=:id AND password=PASSWORD(:password)");
        $stmt->execute(array(":id" => $id, ":password" => $data["password"]));
        $row= $stmt->fetch(PDO::FETCH_ASSOC);
        if($row){
            //updating the profiles table
            $stmt= $pdo->prepare("UPDATE profiles
                                SET forename=:forename, surname=:surname, email=:email, profile_description=:profile_description
                                WHERE member_id=:id");
            $stmt->execute(array(":forename" => $data["forename"],
                                ":surname" => $data["surname"],
                                ":email" => $data["email"],
                                ":profile_description" => $data["profile_description"],
                                ":id" => $id));
            //updating the members table
            $stmt= $pdo->prepare("UPDATE members
                                SET username=:username, password=PASSWORD(:password)
                                WHERE member_id=:id");
            $stmt->execute(array(":username" => $data["username"],
                                ":password" => $data["password"],
                                ":id" => $id));
            return "Details successfully updated.";
        } else {
            return "Wrong password, please try again.";
        }
    }
}

class Post {
    protected $post_id;
    protected $post_date;
    protected $category_id;
    protected $member_id;
    protected $title;
    protected $post_image;
    protected $post_content;
    
    function __construct($post_id, $post_date, $category_id, $member_id, $title, $post_image, $post_content) {
        $this->post_id = $post_id;
        $this->post_date = $post_date;
        $this->category_id = $category_id;
        $this->member_id = $member_id;
        $this->title = $title;
        $this->post_image = $post_image;
        $this->post_content = $post_content;
    }
    
    function new_post($pdo) {
        $sql = "INSERT INTO posts (category_id, member_id, title, post_content, post_image)
                VALUES (:category_id, :member_id, :title, :post_content, :image);";
        $statement = $pdo->prepare($sql);
        $statement->execute([
            'category_id' => $this->category_id,
            'member_id' => $this->member_id,
            'title' => $this->title,
            'post_content' => $this->post_content,
            'image' => $this->post_image
        ]);
    }
    
    function add_hashtags($pdo, $hashtags, $post_id) {
        $hashtag_list = explode(' ', $hashtags);
        
        foreach ($hashtag_list as $hashtag) {
            $sql = "INSERT IGNORE INTO hashtags (hashtag_id)
                    VALUES (:hashtag);";
            $statement = $pdo->prepare($sql);
            $statement->execute([
                'hashtag' => $hashtag
            ]);  
        }
        
        foreach ($hashtag_list as $hashtag) {
            $sql = "INSERT IGNORE INTO posts_hashtags (post_id, hashtag_id)
                    VALUES (:post_id, :hashtag);";
            $statement = $pdo->prepare($sql);
            $statement->execute([
                'post_id' => $post_id,
                'hashtag' => $hashtag
            ]);
        } 
    }
    
    function getPost_id() {
        return $this->post_id;
    }
    function getPost_date() {
        return $this->post_date;
    }
    function getCategory_id() {
        return $this->category_id;
    }
    function getMember_id() {
        return $this->member_id;
    }
    function getTitle() {
        return $this->title;
    }
    function getPost_image() {
        return $this->post_image;
    }
    function getPost_content() {
        return $this->post_content;
    }
    function getAuthorUsername($pdo){
        $author_id= $this->member_id;
        $stmt= $pdo->prepare("SELECT username FROM members WHERE member_id=:member_id");
        $stmt->execute(array(":member_id" => $author_id));
        $row= $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row){
            return $row["username"];
        }
    }
}
class Posts_List {
    private $posts;
    
    function __construct() {
        $this->posts = [];
    }
    
    function generate_posts_list($pdo) {
        $sql = "SELECT p.post_id, p.post_date, p.category_id, p.member_id, pr.forename, p.title, p.post_image, p.post_content
                FROM posts p
                JOIN profiles pr ON pr.member_id = p.member_id
                ORDER BY post_date DESC;";
        $statement = $pdo->prepare($sql);
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        
        while ($post_data = $statement->fetch()) {
            $post_data['title'] = htmlentities($post_data['title'], ENT_SUBSTITUTE, FALSE);
            $post_data['post_content'] = htmlentities($post_data['post_content'], ENT_SUBSTITUTE);
            $post_data['post_date'] = date_format(date_create($post_data['post_date']),"d/m/Y");
            $this->append($post_data);
        }
        $this->posts = json_encode($this->posts);
    }
    
    private function append($post) {
        $this->posts[] = $post;
    }
    
    function get_posts() {
        return $this->posts;
    }
}
class Member_Sign_In {
    private $username;
    private $text_password;
    private $member_id;
    private $security_group;

    function __construct($username, $text_password) {
        $this->username = $username;
        $this->text_password = $text_password;
    }

    function sign_in($pdo) {
        $sql = "SELECT member_id, security_group FROM members WHERE username = :username AND password = PASSWORD(:password);";
        $statement = $pdo->prepare($sql);
        $statement->execute([
            'username' => $this->username,
            'password' => $this->text_password
        ]);

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $this->member_id = $result['member_id'];
            $this->security_group = $result['security_group'];

            $response = "Sign in successful";
        } else {
            $response = "Sign in unsuccessful";
        }
        return $response;
    }

    function create_cookies($remember) {
        date_default_timezone_set('UTC');
        $oneWeek = time() + (60 * 60 * 24 * 7);
        $expiry = $remember ? $oneWeek : 0;

        setcookie('member_id', $this->member_id, $expiry, '/');
        setcookie('security', $this->security_group, $expiry, '/');
    }
}
class rawSearch {
    protected $category;
    protected $hashtag;
    protected $title;
    protected $author;
    function __construct($category, $hashtag, $title, $author) {
        $this->category = $category;
        $this->hashtag = $hashtag;
        $this->title = $title;
        $this->author = $author;
    }
    function getCategoryID($pdo){
        $stmt= $pdo->prepare("SELECT * FROM categories
                                WHERE category_description=:category_description");
        $stmt->execute(array(":category_description" => $this->category));
        $row= $stmt->fetch(PDO::FETCH_ASSOC);
        if($row){
            return $row["category_id"];
        } else {
            return "";
        } 
    }
    function getAuthorID($pdo){
        $stmt= $pdo->prepare("SELECT * FROM members m
                            JOIN posts p ON p.member_id=m.member_id
                            WHERE m.username=:username");
        $stmt->execute(array(":username" => $this->author));
        $row= $stmt->fetch(PDO::FETCH_ASSOC);
        if($row){
            return $row["member_id"];
        } else {
            return "Not an author";
        }
    }
}

class Search {
    protected $category_id;
    protected $hashtag;
    protected $title;
    protected $author_id;
    function __construct($category_id, $hashtag, $title, $author_id) {
        $this->category_id = $category_id;
        $this->hashtag = $hashtag;
        $this->title = $title;
        $this->author_id = $author_id;
    }
    function searchByXParams($pdo){
        $search_results= [];
        $wheres= [];
        $params= [];
        if(!empty($this->category_id)){
            $wheres[]= "p.category_id = :category_id";
            $params[":category_id"]= $this->category_id;
        }
        if(!empty($this->hashtag)){
            $wheres[]= "ph.hashtag_id = :hashtag";
            $params[":hashtag"]= $this->hashtag;
        }
        if(!empty($this->title)){
            $wheres[]= "p.title LIKE CONCAT('%', :title, '%')";
            $params[":title"]= $this->title;
        }
        if(!empty($this->author_id)){
            $wheres[]= "p.member_id = :author_id";
            $params[":author_id"]= $this->author_id;
        }
        $sql= "SELECT p.post_id, p.title, p.post_image, p.post_date, m.username FROM posts p
                JOIN posts_hashtags ph ON ph.post_id=p.post_id
                JOIN members m ON m.member_id=p.member_id";
        if(!empty($wheres)){
            $sql.= " WHERE " . implode(" AND ", $wheres);
        }
        $sql.= " ORDER BY p.post_date DESC";

        $stmt= $pdo->prepare($sql);
        $stmt->execute($params);
        while ($row= $stmt->fetch(PDO::FETCH_ASSOC)){
            $row['post_date'] = date_format(date_create($row['post_date']),"d/m/Y");
            array_push($search_results, $row);
        }
        return $search_results;
    }
}
