$(document).ready(function(){
    $.getJSON("../../models/profileModel.php", function (member){
        $("#profile_image").append("<img class='mb-4' alt='No profile pic' src='../../"+member.profile_image+"' style='max-height:200px;width:auto;'>");
        $("#forename").val(member.forename);
        $("#surname").val(member.surname);
        $("#username").val(member.username);
        $("#email").val(member.email);
        $("#profile_description").val(member.profile_description);
    });
});
