<?php

	class TweetbrowViewController extends BackViewController
	{

        private static function secureUserAPI()
        {
            $data = Core::getParams('post');

            if(empty($data["token"]))
                throw new Exception("Token inexistant.");

            $session = new Session();
            $session->find($data["token"]);

            if($session->user == "")
                throw new Exception("Utilisateur non identifié ou introuvable.");

            $user = new User();
            $user->find($session->user);

            return $user;
        }

        public static function connectAction()
        {

            try {

                $data = Core::getParams('post');

                Core::require_data([
                    $data['login'] => ['notempty','string'],
                    $data['password'] => ['notempty','string']
                ]);

                // Connexion avec email
                if(filter_var($data["login"], FILTER_VALIDATE_EMAIL))
                    $iduser = Database::single("SELECT id FROM users WHERE email = :email", array("email" => $data["login"]));
                // Connexion avec le login
                else
                    $iduser = Database::single("SELECT id FROM users WHERE login = :login", array("login" => $data["login"]));

                $user = new User();
                $user->find($iduser);

                if($user->password != md5($data['password']))
                    throw new Exception("Login ou password incorrect.", 1);

                $session = new Session();
                $session->find(Database::single("SELECT token FROM sessions WHERE user = :userid", array("userid" => $iduser)));

                if($session->token == ""){
                    $session->token = substr(md5(sha1(uniqid())), 0, 20);
                    $session->user = $user->id;
                    $session->ip = $_SERVER["REMOTE_ADDR"];
                    $session->Create();
                }

            } catch (Exception $e) {

                return Core::json(array(), false, $e->getMessage());

            }

            $params = array(
                "id" => $user->id,
                "token" => $session->token,
                "login" => $user->login,
                "pseudo" => $user->pseudo
            );

            return Core::json($params, true, 'Connexion avec succès.');
        }

        public static function logoutAction()
        {
            try {

                self::secureUserAPI();

                $token = Core::getParam('token');

                $session = new Session();
                $session->delete($token);

                return Core::json(array(), true, "Déconnecté avec succès.");

            } catch(Exception $e) {

                return Core::json(array(), false, $e->getMessage());

            }
        }

        public static function registerAction()
        {
            try {

                $data = Core::getParams('post');

                if(empty($data["login"]) || empty($data["pseudo"]) || empty($data["password"]) || empty($data["email"]))
                    throw new Exception("Informations manquantes !");

                if(!filter_var($data["email"], FILTER_VALIDATE_EMAIL))
                    throw new Exception("Email invalide !");

                $user = new User();
                $user->login = $data["login"];
                $user->pseudo = $data["pseudo"];
                $user->password = md5($data["password"]);
                $user->email = $data["email"];
                $created = $user->Create();

                if(!$created)
                    throw new Exception("Login déjà existant !");

                return Core::json(array(), true, "Enregistré avec succès.");

            } catch(Exception $e) {

                return Core::json(array(), false, $e->getMessage());

            }
        }

        public static function timelineAction()
        {
            try {

                $user = self::secureUserAPI();

                $bind = array(
                    "author" => $user->id,
                    "author2" => $user->id,
                    "author3" => $user->id
                );
                $all_tweets = Database::query("SELECT id, author, message, date_create, response
                                              FROM tweets
                                              WHERE author = :author
                                              OR author
                                                  IN (SELECT id_following
                                                      FROM follows
                                                      WHERE id_follower = :author2)
                                              OR id
                                                  IN (SELECT id_tweet
                                                      FROM retweets
                                                      WHERE id_user = :author3)
                                              ORDER BY date_create DESC", $bind);

                if(!$all_tweets && !empty($all_tweets))
                    throw new Exception("Erreur !");

                foreach($all_tweets as $key => $tweet) {
                    $query = Database::query("SELECT login, pseudo FROM users WHERE id = :iduser", array("iduser" => $tweet["author"]));

                    unset($all_tweets[$key]["author"]);

                    $all_tweets[$key]["login"] = $query[0]["login"];
                    $all_tweets[$key]["pseudo"] = $query[0]["pseudo"];

                    $params = array(
                        "idtweet" => $tweet["id"],
                        "iduser" => $user->id
                    );

                    $retweet = Database::single("SELECT COUNT(*) FROM retweets WHERE id_tweet = :idtweet AND id_user = :iduser", $params);
                    $all_tweets[$key]["retweet"] = ($retweet == "1") ? true : false;

                    $favoris = Database::single("SELECT COUNT(*) FROM favoris WHERE id_tweet = :idtweet AND id_user = :iduser", $params);
                    $all_tweets[$key]["favoris"] = ($favoris == "1") ? true : false;

                }

                return Core::json($all_tweets, true, 'Listage des tweets avec succès.');

            } catch (Exception $e) {

                return Core::json(array(), false, $e->getMessage());

            }

        }


        public static function followAction($id_following)
        {
            try {

                $user = self::secureUserAPI();

                $bind = array(
                    "id_follower" => $user->id,
                    "id_following" => $id_following
                );
                $insert = Database::query("INSERT INTO follows (id_follower, id_following) VALUES (:id_follower, :id_following)", $bind);

                if(!$insert)
                    throw new Exception("Vous suivez déjà cet utilisateur.");

                return Core::json(array(), true, 'Suivi avec succès.');

            } catch (Exception $e) {

                return Core::json(array(), false, $e->getMessage());

            }
        }

        public static function unfollowAction($id_following)
        {
            try {

                $user = self::secureUserAPI();

                $bind = array(
                    "id_follower" => $user->id,
                    "id_following" => $id_following
                );
                $delete = Database::query("DELETE FROM follows WHERE id_follower = :id_follower AND id_following = :id_following", $bind);

                if(!$delete)
                    throw new Exception("Follow inexistant !");

                return Core::json(array(), true, 'Follow supprimed !');

            } catch (Exception $e) {

                return Core::json(array(), false, $e->getMessage());

            }
        }

        public static function userAction($action)
        {
            try {

                $me = self::secureUserAPI();

                $all_users = Database::query("SELECT id, login, pseudo FROM users ORDER BY id DESC");

                if(!$all_users && !empty($all_users))
                    throw new Exception("Erreur !");

                foreach($all_users as $key => $user) {

                    $follow = Database::single("SELECT COUNT(id_follower) FROM follows WHERE id_follower = :idme AND id_following = :idfollowing", array("idme" => $me->id, "idfollowing" => $user["id"]));

                    $all_users[$key]["followed"] = ($follow == 1) ? true : false;

                }

                return Core::json($all_users, true, 'Listage des tweets avec succès.');

            } catch (Exception $e) {

                return Core::json(array(), false, $e->getMessage());

            }
        }

        public static function tweetAction($action)
        {
            try {

                $user = self::secureUserAPI();

                switch ($action) {
                    case 'add':
                        return self::tweetAdd($user);
                        break;

                    case 'delete':
                        return self::tweetDelete($user);
                        break;

                    case 'retweet':
                        return self::tweetRetweet($user);
                        break;

                    case 'unretweet':
                        return self::tweetUnRetweet($user);
                        break;

                    case 'favoris':
                        return self::tweetFavoris($user);
                        break;

                    case 'unfavoris':
                        return self::tweetUnFavoris($user);
                        break;

                    default:
                        throw new Exception("Action inconnue.");
                        break;
                }

            } catch (Exception $e) {

                return Core::json(array(), false, $e->getMessage());

            }

        }

        private static function tweetAdd($user)
        {
            $message = trim(Core::getParam("message"));
            $id_parent = Core::getParam("id_parent");

            if(empty($message))
                throw new Exception("Message vide.");
            else if(strlen($message) > 140)
                throw new Exception("Message supérieur à 140 caractères.");

            $tweet = new Tweet();
            $tweet->author = $user->id;
            $tweet->message = $message;

            if(!is_null($id_parent))
                $tweet->response = $id_parent;

            $success = $tweet->Create();

            if(!$success)
                throw new Exception("Erreur lors de l'enregistrement du tweet.");

            return Core::json(array("new_id" => $tweet->lastInsertId()), true, 'Tweet enregistré avec succès.');

        }

        private static function tweetDelete($user)
        {
            $tweetid = Core::getParam("tweet_id");

            if(empty($tweetid) || !is_numeric($tweetid))
                throw new Exception("Tweet id non renseigné ou mauvais format !");

            $tweet = new Tweet();
            $tweet->find($tweetid);

            if($tweet->id == "")
                throw new Exception("Tweet inexistant.");
            else if($tweet->author != $user->id)
                throw new Exception("Vous ne pouvez pas supprimer ce tweet !");

            $bind = array(
                "idtweet" => $tweet->id
            );
            Database::query("DELETE FROM retweets WHERE id_tweet = :idtweet", $bind);
            Database::query("DELETE FROM favoris WHERE id_tweet = :idtweet", $bind);

            if(!$tweet->delete())
                throw new Exception("Erreur lors de la suppression..");

            return Core::json(array(), true, "Tweet supprimé avec succès !");


        }

        private static function tweetRetweet($user)
        {
            $tweetid = Core::getParam("tweet_id");

            if(empty($tweetid) || !is_numeric($tweetid))
                throw new Exception("Tweet id non renseigné ou mauvais format !");

            $tweet = new Tweet();
            $tweet->find($tweetid);

            if($tweet->id == "")
                throw new Exception("Tweet inexistant !");

            $bind = array(
                "idtweet" => $tweetid,
                "iduser"  => $user->id
            );
            $insert = Database::query("INSERT INTO retweets (id_tweet, id_user) VALUES (:idtweet, :iduser)", $bind);

            if(!$insert)
                throw new Exception("Erreur lors du retweet !");

            return Core::json(array(), true, "Retweeter avec succès !");
        }

        private static function tweetUnRetweet($user)
        {
            $tweetid = Core::getParam("tweet_id");

            if(empty($tweetid) || !is_numeric($tweetid))
                throw new Exception("Tweet id non renseigné ou mauvais format !");

            $bind = array(
                "idtweet" => $tweetid,
                "iduser"  => $user->id
            );
            $delete = Database::query("DELETE FROM retweets WHERE id_tweet = :idtweet AND id_user = :iduser", $bind);

            if(!$delete)
                throw new Exception("Erreur lors de l'annulation du retweet !");

            return Core::json(array(), true, "Retweet annulé !");
        }

        private static function tweetFavoris($user)
        {
            $tweetid = Core::getParam("tweet_id");

            if(empty($tweetid) || !is_numeric($tweetid))
                throw new Exception("Tweet id non renseigné ou mauvais format !");

            $tweet = new Tweet();
            $tweet->find($tweetid);

            if($tweet->id == "")
                throw new Exception("Tweet inexistant !");

            $bind = array(
                "idtweet" => $tweetid,
                "iduser"  => $user->id
            );

            $insert = Database::query("INSERT INTO favoris (id_tweet, id_user) VALUES (:idtweet, :iduser)", $bind);

            if(!$insert)
                throw new Exception("Erreur lors du fav !");

            return Core::json(array(), true, "Favorisé avec succès !");
        }

        private static function tweetUnFavoris($user)
        {
            $tweetid = Core::getParam("tweet_id");

            if(empty($tweetid) || !is_numeric($tweetid))
                throw new Exception("Tweet id non renseigné ou mauvais format !");

            $bind = array(
                "idtweet" => $tweetid,
                "iduser"  => $user->id
            );

            $delete = Database::query("DELETE FROM favoris WHERE id_tweet = :idtweet AND id_user = :iduser", $bind);

            if(!$delete)
                throw new Exception("Erreur lors de l'annulation du fav !");

            return Core::json(array(), true, "Défavorisé avec succès !");
        }


    }