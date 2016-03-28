<?php

namespace Lyber\Apps\Admin\Bundles\Users\Controller;

use Exception;
use Lyber\Common\Components\Auth;
use Lyber\Common\Components\Core;
use Lyber\Common\Components\Database;
use Lyber\Common\Controllers\BundleController;
use Lyber\Common\Entities\User;

class UsersBundleController extends BundleController
{

	public static function indexAction($params){

		$users = Database::query('SELECT * FROM users');

		$return = array(
			'users' => $users
		);

		$deleteAction = Core::getParam('delete',false,'get');
		if($deleteAction == "success"){

			$return['alert']['success'] = "Utilisateur supprimé avec succès !";

		}

		$deleteAction = Core::getParam('add',false,'get');
		if($deleteAction == "success"){

			$return['alert']['success'] = "Utilisateur enregistré avec succès !";

		}

		return $return;
	}

	public static function manageAction($id){

		$return = array();
		$return['alert']['success'] = array();
		$return['alert']['error'] = array();

		$user = new User();
		$user->find($id);

		$post = Core::getParams('post');
		if(!empty($post)){

			try {

				if (filter_var($post['email'], FILTER_VALIDATE_EMAIL) === false) {
					throw new Exception("Email invalide");
				}
				else if(empty($post['password']) && empty($id)){
					throw new Exception("Mot de passe non renseigné");
				}
				else if(!empty($post['password']) && $post['password'] != $post['repassword']){
					throw new Exception("Les mots de passe sont différents");
				}

				$user->setEmail($post['email']);
				$user->setFirstName($post['firstname']);
				$user->setLastname($post['lastname']);

				if(!empty($post['password'])){
					$user->setPassword($post['password']);
				}

				$save = (!empty($id)) ? $user->save() : $user->create();

				if($save == 1){
					if(!empty($id)){
						$return['alert']['success'][] = "Modifications enregistrées avec succès.";
					}
					else {
						Core::redirect(Core::absURL().'admin/users?add=success');
					}
				}

			} catch (Exception $e) {

				$return['alert']['error'][] = $e->getMessage();

			}

		}

		$return['user'] = array(
			'id_user' => $user->getId_user(),
			'email' => $user->getEmail(),
			'firstname' => $user->getFirstName(),
			'lastname' => $user->getLastName()
		);

		return $return;
	}

	public static function deleteAction($id){

		$user = new User();
		$user->find($id);

		if(!$user->isEmpty()){
			try {

				$delete = Database::query('DELETE FROM sessions WHERE id_user = :id_user', array('id_user' => $id));
				$user->delete();
				Core::redirect(Core::absURL().'admin/users?delete=success');

			} catch(Exception $e) {
				Core::redirect(Core::absURL().'admin/users?delete=error');
			}
		}

	}

}