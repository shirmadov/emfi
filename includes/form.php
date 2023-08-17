<?php

require_once "../controller/ControllerAmo.php";

if(isset($_POST['send'])){

    $name = $_POST['name'];
    $phone = $_POST['number'];
    $email = $_POST['email'];
    $target = $_POST['target'];
    $company = $_POST['company'];

    $form = new ControllerAmo();

    if($form->sendForm( $name, $phone, $email, $target, $company)){
        header("Location: ../index.php?error=none");
    }
}