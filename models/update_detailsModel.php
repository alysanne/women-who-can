<?php
session_start();
include "class_lib.php";
include "../connection.php";
$pdo = DB::getInstance();

$member_id= filter_input(INPUT_COOKIE, 'member_id', FILTER_SANITIZE_STRING);

//fetching details and creating a Member object
$stmt= $pdo->prepare("SELECT * FROM members m
                        JOIN profiles p ON m.member_id=p.member_id
                        WHERE m.member_id=:id");
$stmt->execute(array(":id" => $member_id));
$row= $stmt->fetch(PDO::FETCH_ASSOC);
$member= new Member($row["member_id"], htmlentities($row["username"]), htmlentities($row["password"]),
                            $row["security_group"], $row["registration_date"], htmlentities($row["email"]),
                            $row["profile_image"], htmlentities($row["forename"]), htmlentities($row["surname"]),
                            htmlentities($row["profile_description"]));
//updating details
if(!empty($_REQUEST["password"])){
    $member->updateDetails($pdo, $_REQUEST, $_SESSION["id"]);
    header("Location: ../views/pages/profile.php");
    return;
}