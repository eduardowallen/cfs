<?php
class LockedController extends Controller {

    public function index(){
        $this->set('locked_msg', 'This event has been locked');
    }

}