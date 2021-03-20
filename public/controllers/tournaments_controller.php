<?php
class TournamentsController extends Controller {
    function index() {
        $player         = Player::get_instance();
        $tournaments    = Tournament::all();

        $this->assign('player',         $player);
        $this->assign('tournaments',    $tournaments);
    }
}
?>	