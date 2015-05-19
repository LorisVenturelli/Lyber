<?php

	class TweetbrowViewController extends ModuleViewController
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
            $data = Core::getParams('post');

            try {

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
                "token" => $session->token,
                "login" => $user->login,
                "email" => $user->email,
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

        public static function timelineAction()
        {
            try {

                $user = self::secureUserAPI();

                $bind = array(
                    "author" => $user->id,
                    "author2" => $user->id,
                    "author3" => $user->id
                );
                $all_tweets = Database::query("SELECT *
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

                if(!$all_tweets)
                    throw new Exception("Erreur !");
                else
                    return Core::json($all_tweets, true, 'Listage des tweets avec succès.');

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
            $id_parent = Core::getParam("tweet_parent");

            if(empty($message))
                throw new Exception("Message vide.");
            else if(strlen($message) > 140)
                throw new Exception("Message supérieur à 140 caractères.");

            $tweet = new Tweet();
            $tweet->author = $user->id;
            $tweet->message = $message;

            if(!empty($id_parent))
                $tweet->response = $id_parent;

            $success = $tweet->Create();

            if(!$success)
                throw new Exception("Erreur lors de l'enregistrement du tweet.");
            else
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
            else
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
            else
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
            else
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
            else
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
            else
                return Core::json(array(), true, "Défavorisé avec succès !");
        }


    }