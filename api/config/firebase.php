<?php
require __DIR__ . '/../../vendor/autoload.php'; 
use Kreait\Firebase\Factory;
use Dotenv\Dotenv;

function get_firebase_auth() {
    static $auth = null;

    if ($auth === null) {
        if (!isset($_ENV['FIREBASE_CREDENTIALS'])) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../'); 
            $dotenv->load();
        }

        $factory = (new Factory)
            ->withServiceAccount($_ENV['FIREBASE_CREDENTIALS']);
            
        $auth = $factory->createAuth();
    }
    return $auth;
}

function get_firebase_firestore() {
    static $firestore = null;

    if ($firestore === null) {
        if (!isset($_ENV['FIREBASE_CREDENTIALS'])) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../'); 
            $dotenv->load();
        }
        
        $factory = (new Factory)
            ->withServiceAccount($_ENV['FIREBASE_CREDENTIALS']);
            
        $firestore = $factory->createFirestore();
    }
    return $firestore;
}
