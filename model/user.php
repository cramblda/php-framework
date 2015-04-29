<?php

class user {

    private $email;
    private $firstName;
    private $lastName;
    private $password;
    private $role = array();
    private $salt;
    private $username;

    public function __construct () {

    }

    // Getter Methods
    public function getEmail() {
        return $this->email;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getRole() {
        return $this->role;
    }

    public function getSalt() {
        return $this->salt;
    }

    public function getUsername() {
        return $this->username;
    }


    // Setter Methods
    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    public function setFirstName($firstName) {
        $this->firstName = $firstName;

        return $this;
    }

    public function setLastName($lastName) {
        $this->lastName = $lastName;

        return $this;
    }

    public function setPassword($password) {
        $this->password = $password;

        return $this;
    }

    public function setRole($role) {
        $this->role = $role;

        return $this;
    }

    public function setSalt($salt) {
        $this->salt = $salt;

        return $this;
    }

    public function setUsername($username) {
        $this->username = $username;

        return $this;
    }


    //Additional  methods
    public function getName() {
        return $this->firstName . ' ' . $this->lastName;
    }


}
