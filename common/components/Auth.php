<?php

namespace Lyber\Common\Components;

use Exception;
use Lyber\Common\Entities\User;

class Auth {

    const NAME_AUTH = "lyber";

    public static function hashPassword($password) {

        return hash('sha512', Config::get('global', 'key_crypt') . $password);
    }

    public static function login($email, $password) {

        if (empty($email) || empty($password) || filter_var($email, FILTER_VALIDATE_EMAIL) === false)
            throw new Exception('Email ou password non renseigné');

        $id_user = Database::single("SELECT id_user FROM users WHERE email = :email AND password = :password", array('email' => $email, 'password' => self::hashPassword($password)));

        $user = new User();
        $user->find($id_user);

        if (empty($id_user))
            throw new Exception('Email ou mot de passe incorrect');


        $token = substr(md5(sha1(time())), 0, 15);

        $token_old = Database::single('SELECT token FROM sessions WHERE id_user = :id_user', array('id_user' => $id_user));

        $data_sessions = array(
            'id_user' => $id_user,
            'token' => $token,
            'ip' => $_SERVER['REMOTE_ADDR']
        );

        if (empty($token_old)) {

            $save = Database::query('INSERT INTO sessions (id_user, token, ip) VALUES (:id_user, :token, :ip)', $data_sessions);
        } else {

            $save = Database::query('UPDATE sessions SET token = :token, ip = :ip WHERE id_user = :id_user', $data_sessions);
        }

        if ($save === 0)
            throw new Exception('Erreur lors de la création de la session');

        $cookie = array(
            'token' => $token,
            'date_limit' => (time() + Cookie::OneDay)
        );

        Cookie::set(self::NAME_AUTH, Encryption::encrypt(json_encode($cookie)));

        return true;
    }

    public static function logout() {
        return Cookie::Delete(self::NAME_AUTH);
    }

    public static function register($email, $password, $firstname, $lastname) {

        $user = new User();
        $user->email = $email;
        $user->password = self::hashPassword($password);
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->Create();
    }

    public static function isLogged() {

        $data = Cookie::Get(self::NAME_AUTH);

        if (empty($data)) {
            return false;
        }

        $data = json_decode(Encryption::decrypt($data));

        $id_user = Database::single('SELECT id_user FROM sessions WHERE token = :token', array('token' => $data->token));

        return (!empty($id_user)) ? $id_user : false;
    }

    public static function lock() {

        $data = Cookie::Get(self::NAME_AUTH);

        if (empty($data)) {
            return false;
        }

        $data = json_decode(Encryption::decrypt($data));
        $data->date_limit = time() - 1;

        Cookie::set(self::NAME_AUTH, Encryption::encrypt(json_encode($data)));

        return true;
    }

    public static function isLocked() {

        $data = Cookie::Get(self::NAME_AUTH);

        if (empty($data)) {
            return false;
        }

        $data = json_decode(Encryption::decrypt($data));

        return ($data->date_limit <= time());
    }

}
