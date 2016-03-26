<?php

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

    }