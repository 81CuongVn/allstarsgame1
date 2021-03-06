<?php
if(isset($_SESSION['user_id']) && $_SESSION['user_id']) {
    User::set_instance(User::find($_SESSION['user_id']));
}

if (!isset($_SESSION['orig_user_id'])) {
    $_SESSION['orig_user_id']	= 0;
}

if(isset($_SESSION['user_id']) && $_SESSION['user_id']) {
    if(User::get_instance()->session_key != session_id() && !$_SESSION['universal']) {
        session_destroy();

        redirect_to();
    }
}