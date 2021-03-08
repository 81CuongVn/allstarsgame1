<?php
if(isset($_SESSION['user_id']) && $_SESSION['user_id']) {
    $user = User::find($_SESSION['user_id']);
    User::set_instance($user);
}

if(isset($_SESSION['user_id']) && $_SESSION['user_id']) {
    if(User::get_instance()->session_key != session_id() && !$_SESSION['universal']) {
        session_destroy();

        redirect_to();
    }
}