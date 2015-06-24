<?php

	class CovoicarViewController extends ModuleViewController
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


        public static function carAction($action){

            try {

                $user = self::secureUserAPI();

                switch ($action) {
                    case 'add':
                        return self::carAdd($user);
                        break;

                    case 'edit':
                        return self::carEdit($user);
                        break;

                    case 'delete':
                        return self::carDelete($user);
                        break;

                    default:
                        throw new Exception("Action inconnue.");
                        break;
                }

            } catch (Exception $e) {

                return Core::json(array(), false, $e->getMessage());

            }

        }

        private static function carAdd($user)
        {
            $data = Core::getParams('post');

            if(empty($data['brand'])
                || empty($data['model'])
                || empty($data['comfort'])
                || empty($data['place'])
                || empty($data['color'])
            ){
                throw new Exception("Informations manquantes !");
            }

            $car = new Car();
            $car->id_user = $user->id;
            $car->brand = $data['brand'];
            $car->model = $data['model'];
            $car->comfort = $data['comfort'];
            $car->place = $data['place'];
            $car->color = $data['color'];
            $created = $car->Create();

            if(!$created)
                throw new Exception("Erreur lors de la création de la voiture !");
            else
                return Core::json(array(), true, "Voiture enregistrée avec succès !");
        }

        private static function carEdit($user)
        {
            $data = Core::getParams('post');

            if(empty($data["id_car"]))
                throw new Exception("id_car non renseigné !");

            $car = new Car($data["id_car"]);

            if(empty($car->user_id))
                throw new Exception("Voiture non trouvée !");

            if(!empty($data["brand"]))
                $car->brand = $data["brand"];

            if(!empty($data["model"]))
                $car->model = $data["model"];

            if(!empty($data["comfort"]))
                $car->comfort = $data["comfort"];

            if(!empty($data["place"]))
                $car->place = $data["place"];

            if(!empty($data["color"]))
                $car->color = $data["color"];

            $car->save();

            return Core::json(array(), true, "Voiture éditée avec succès !");
        }

        private static function carDelete($user)
        {
            $id_car = Core::getParam("id_car");

            if(empty($id_car) || !is_numeric($id_car))
                throw new Exception("id_car non renseigné ou mauvais format !");

            $car = new Car();
            $car->find($id_car);

            if($car->id == "")
                throw new Exception("Car inexistant.");
            else if($car->user_id != $user->id)
                throw new Exception("Vous ne pouvez pas supprimer cette voiture !");

            if(!$car->delete())
                throw new Exception("Erreur lors de la suppression..");

            return Core::json(array(), true, "Voiture supprimée avec succès !");
        }

        public static function userAction($action){

            try {

                switch ($action) {

                    case 'add':
                        return self::userAdd();
                        break;

                    case 'connect':
                        return self::userConnect();
                        break;

                    case 'logout':
                        $user = self::secureUserAPI();
                        return self::userLogout($user);
                        break;

                    case 'edit':
                        $user = self::secureUserAPI();
                        return self::userEdit($user);
                        break;

                    case 'delete':
                        $user = self::secureUserAPI();
                        return self::userDelete($user);
                        break;

                    default:
                        throw new Exception("User action inconnue.");
                        break;
                }

            } catch (Exception $e) {

                return Core::json(array(), false, $e->getMessage());

            }

        }


        private static function userAdd()
        {
            $data = Core::getParams('post');

            if(empty($data['email'])
                || empty($data['password'])
                || empty($data['repassword'])
                || empty($data['lastname'])
                || empty($data['firstname'])
                || empty($data['gender'])
                || empty($data['birthday'])
            ){
                throw new Exception("Informations manquantes !");
            }

            if(!filter_var($data["email"], FILTER_VALIDATE_EMAIL))
                throw new Exception("Email invalide !");
            else if(strlen($data['password']) < 4)
                throw new Exception("Mot de passe trop court !");
            else if($data["password"] != $data["repassword"])
                throw new Exception("Les 2 mots de passes correspondent pas !");

            $user = new User();
            $user->email = $data["email"];
            $user->password = md5($data["password"]);
            $user->lastname = $data["lastname"];
            $user->firstname = $data["firstname"];
            $user->gender = $data["gender"];
            $user->birthday = $data["birthday"];
            $created = $user->Create();

            if(!$created)
                throw new Exception("Email déjà existant !");

            return Core::json(array(), true, "Enregistré avec succès.");
        }

        private static function userConnect()
        {
            $data = Core::getParams('post');

            Core::require_data([
                $data['email'] => ['notempty','string'],
                $data['password'] => ['notempty','string']
            ]);

            // Connexion avec email
            if(!filter_var($data["email"], FILTER_VALIDATE_EMAIL))
                throw new Exception("Format de l'email invalide !");

            $iduser = Database::single("SELECT id FROM users WHERE email = :email", array("email" => $data["email"]));

            $user = new User();
            $user->find($iduser);

            if($user->password != md5($data['password']))
                throw new Exception("Email ou password incorrect.", 1);

            $session = new Session();
            $session->find(Database::single("SELECT token FROM sessions WHERE user = :userid", array("userid" => $iduser)));

            if($session->token == ""){
                $session->token = substr(md5(sha1(uniqid())), 0, 20);
                $session->user = $user->id;
                $session->ip = $_SERVER["REMOTE_ADDR"];
                $session->Create();
            }

            $params = array(
                "id" => $user->id,
                "token" => $session->token
            );

            return Core::json($params, true, 'Connexion avec succès.');
        }


        private static function userLogout()
        {
            $token = Core::getParam('token');

            $session = new Session($token);
            $session->delete();

            return Core::json(array(), true, "Déconnecté avec succès.");
        }

        private static function userEdit($user)
        {
            $data = Core::getParams('post');

            if(!empty($data["email"]))
                $user->email = $data["email"];

            if(!empty($data["password"]))
                $user->password = md5($data["password"]);

            if(!empty($data["lastname"]))
                $user->lastname = $data["lastname"];

            if(!empty($data["firstname"]))
                $user->firstname = $data["firstname"];

            if(!empty($data["phone"]))
                $user->phone = $data["phone"];

            if(!empty($data["bio"]))
                $user->bio = $data["bio"];

            if(!empty($data["birthday"]))
                $user->birthday = $data["birthday"];

            if(!empty($data["gender"]))
                $user->gender = $data["gender"];

            $user->save();

            return Core::json(array(), true, "User édité avec succès !");
        }

        private static function userDelete($user)
        {
            $params = array(
                'userid' => $user->id
            );

            $session = Database::query("DELETE FROM sessions WHERE user = :userid", $params);
            $car = Database::query("DELETE FROM cars WHERE user_id = :userid", $params);
            $trip = Database::query("DELETE FROM trips WHERE driver = :userid", $params);
            $travels = Database::query("DELETE FROM travels WHERE id_user = :userid", $params);

            if(!$user->delete())
                throw new Exception("Erreur lors de la suppression de l'user ..");

            return Core::json(array(), true, "User supprimé avec succès !");
        }

        public static function tripAction($action){

            try {

                $user = self::secureUserAPI();

                switch ($action) {
                    case 'add':
                        return self::tripAdd($user);
                        break;

                    case 'edit':
                        return self::tripEdit($user);
                        break;

                    case 'delete':
                        return self::tripDelete($user);
                        break;

                    default:
                        throw new Exception("Action trip inconnue.");
                        break;
                }

            } catch (Exception $e) {

                return Core::json(array(), false, $e->getMessage());

            }

        }

        private static function tripAdd($user)
        {
            $data = Core::getParams('post');

            if(empty($data['start'])
                || empty($data['arrival'])
                || empty($data['highway'])
                || empty($data['hourStart'])
                || empty($data['price'])
                || empty($data['place'])
            ){
                throw new Exception("Informations manquantes !");
            }

            if(!is_string($data["start"]) || !is_string($data["arrival"]))
                throw new Exception("start ou arrival n'est pas un string !");

            $trip = new Trip();
            $trip->driver = $user->id;
            $trip->start = $data['start'];
            $trip->arrival = $data['arrival'];
            $trip->highway = $data['highway'];
            $trip->hourStart = $data['hourStart'];
            $trip->price = $data['price'];
            $trip->place = $data['place'];

            if(!empty($data['comment']))
                $trip->comment = $data['comment'];

            if(!empty($data['roundTrip'])){
                $trip_back = new Trip();
                $trip_back->driver = $user->id;
                $trip_back->start = $data['arrival'];
                $trip_back->arrival = $data['start'];
                $trip_back->highway = $data['highway'];
                $trip_back->hourStart = $data['roundTrip'];
                $trip_back->price = $data['price'];
                $trip_back->place = $data['place'];
                $created_back = $trip_back->Create();
            }

            if($created_back)
                $trip->roundTrip = $trip_back->lastInsertId();

            $created = $trip->Create();

            if(!$created)
                throw new Exception("Erreur lors de la création de la voiture !");

            return Core::json(array(), true, "Trip enregistré avec succès !");
        }

        private static function tripEdit($user)
        {
            $data = Core::getParams('post');

            if(empty($data["id_trip"]))
                throw new Exception("id_trip non renseigné !");

            $trip = new Trip($data["id_trip"]);

            if(empty($trip->user_id))
                throw new Exception("Trip non trouvée !");

            if(!empty($data["start"]))
                $trip->start = $data["start"];

            if(!empty($data["arrival"]))
                $trip->arrival = $data["arrival"];

            if(!empty($data["highway"]))
                $trip->highway = $data["highway"];

            if(!empty($data["hourStart"]))
                $trip->hourStart = $data["hourStart"];

            if(!empty($data["price"]))
                $trip->price = $data["price"];

            if(!empty($data["place"]))
                $trip->place = $data["place"];

            if(!empty($data["comment"]))
                $trip->comment = $data["comment"];

            $trip->save();

            return Core::json(array(), true, "Trip éditée avec succès !");
        }

        private static function tripDelete($user)
        {
            $id_trip = Core::getParam("id_trip");

            if($id_trip == "" || !is_numeric($id_trip))
                throw new Exception("id_trip non renseigné ou mauvais format !");

            $trip = new Trip();
            $trip->find($id_trip);

            if($trip->id == "")
                throw new Exception("Trip inexistant.");
            else if($trip->driver != $user->id)
                throw new Exception("Vous ne pouvez pas supprimer cette voiture !");

            if(!$trip->delete())
                throw new Exception("Erreur lors de la suppression..");

            Database::query("DELETE FROM trips WHERE id = :roundtrip", array('roundtrip' => $trip->roundTrip));

            return Core::json(array(), true, "Trip supprimée avec succès !");
        }

        public static function travelAction($action){

            try {

                $user = self::secureUserAPI();

                switch ($action) {
                    case 'add':
                        return self::travelAdd($user);
                        break;

                    case 'delete':
                        return self::travelDelete($user);
                        break;

                    default:
                        throw new Exception("Action travel inconnue.");
                        break;
                }

            } catch (Exception $e) {

                return Core::json(array(), false, $e->getMessage());

            }

        }

        private static function travelAdd($user)
        {

            $id_trip = Core::getParam('id_trip');

            if(empty($id_trip))
                throw new Exception("id_trip manquant !");

            $params = array(
                'id_user' => $user->id,
                'id_trip' => $id_trip
            );
            $insert = Database::query("INSERT INTO travels (id_user, id_trip) VALUES (:id_user, :id_trip)", $params);

            if(!$insert)
                throw new Exception("Vous participez déjà au voyage.");

            return Core::json(array(), true, "Voyage enregistré avec succès !");
        }

        private static function travelDelete($user)
        {
            $id_trip = Core::getParam("id_trip");

            if($id_trip == "" || !is_numeric($id_trip))
                throw new Exception("id_trip non renseigné ou mauvais format !");

            $params = array(
                'id_user' => $user->id,
                'id_trip' => $id_trip
            );
            $delete = Database::query("DELETE FROM travels WHERE id_user = :id_user AND id_trip = :id_trip", $params);

            if(!$delete)
                throw new Exception("Ce voyage n'existe pas !");

            return Core::json(array(), true, "Voyage supprimée avec succès !");
        }




    }