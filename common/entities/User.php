<?php

namespace Lyber\Common\Entities;

use Lyber\Common\Components\Auth;
use Lyber\Common\Components\Config;
use Lyber\Common\Components\Crud;

class User extends Crud {

    # The table you want to perform the database actions on
    protected $table = 'users';

    # Primary Key of the table
    protected $pk  = 'id_user';

    public static function getInstance(){

        $id_user = Auth::isLogged();
        if($id_user === false){
            return null;
        }

        $user = new User();
        $user->find($id_user);

        return $user;

    }

    /**
     * @return int
     */
    public function getId_user(){
        return (int) $this->id_user;
    }

    /**
     * @return string
     */
    public function getEmail(){
        return (string) $this->email;
    }
    /**
     * @param string $email
     */
    public function setEmail($email){
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPassword(){
        return (string) $this->password;
    }
    /**
     * @param string $password
     */
    public function setPassword($password){
        $this->password = hash('sha512', Config::get('global', 'key_crypt') . $password);
    }

    /**
     * @return string
     */
    public function getFirstName(){
        return (string) $this->firstname;
    }
    /**
     * @param string $firstname
     */
    public function setFirstName($firstname){
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getLastName(){
        return (string) $this->lastname;
    }
    /**
     * @param string $lastname
     */
    public function setLastname($lastname){
        $this->lastname = $lastname;
    }

    /**
     * @return string
     */
    public function getFullName(){
        return (string) $this->getFirstName() . " " . $this->getLastName();
    }

    /**
     * @return bool
     */
    public function isEmpty(){
        return ($this->getId_user() == "");
    }
}