<?php

namespace Lyber\Common\Entities;

use Lyber\Common\Components\Crud;

class Session extends Crud {

    # The table you want to perform the database actions on
    protected $table = 'sessions';

    # Primary Key of the table
    protected $pk  = 'token';

    /**
     * @return User
     */
    public function getUser(){
        $user = new User();
        $user->find($this->id_user);

        return $user;
    }
    /**
     * @param integer $id_user
     */
    public function setId_user($id_user){
        $this->id_user = $id_user;
    }

    /**
     * @return string
     */
    public function getToken(){
        return (string) $this->token;
    }

    /**
     * @return string
     */
    public function getIp(){
        return (string) $this->ip;
    }
    /**
     * @param string $ip
     */
    public function setIp($ip){
        $this->ip = $ip;
    }

}