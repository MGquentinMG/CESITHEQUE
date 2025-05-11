<?php
// src/Controller/HomeController.php

class HomeController {
    private \Twig\Environment $twig;

    public function __construct(\Twig\Environment $twig) {
        $this->twig = $twig;
    }

    public function index() {
        echo $this->twig->render('home.twig');
    }

    public function A_Propos() {
        echo $this->twig->render('A_Propos.twig');
    }

    public function showContactForm()
    {
        echo $this->twig->render('Contact.twig');
    }


}
