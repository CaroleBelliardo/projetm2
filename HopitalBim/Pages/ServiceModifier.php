<?php

	include ('../Config/Menupage.php');
	$lien ='ServiceModifier.php';

	if(isset($_POST['btn-modifier']))
{


	// Recuperation des champs entrés dans le formulaire : 
	// recuperation des information relatif à la table Services
	$text_nomService = ucfirst(trim($_POST['text_nomService']));	
	$text_telephone = strip_tags($_POST['text_telephone']);	
	$text_mail = $text_nomService."@hopitalbim.fr";   // l'adresse mail sera toujours = au nom de service+@hotpitalbim.fr
	$text_ouverture = date('h:i', strtotime($_POST['text_ouverture']));
	$text_fermeture = date('h:i', strtotime($_POST['text_fermeture'])); 
	// recuperation des information relatif à la table LocalisationServices
	$text_batiment = $_POST['text_batiment'];	
	$text_etage = $_POST['text_etage'];	
	$text_aile = $_POST['text_aile'];	
	
	// TEST si le service est deja present : 
	$stmt = $auth_user->runQuery("SELECT nomService FROM Services WHERE nomService=:nomService ");
	$stmt->execute(array('nomService'=>$text_nomService));
	$rechercheService=$stmt->fetch(PDO::FETCH_ASSOC);
	echo $rechercheService['nomService'];
		// Apres avoir realisé une requete pour rechercher les services, on va tester si celui est present dans la bdd
	if($text_nomService=="")	{
		$error[] = "Il faut ajouter un nom de service"; }
	else if ($rechercheService['nomService']=="" or $rechercheService['nomService']==$serviceInfo['nomService'])
	{
		try
		{
			// Ajout de la localisation en premier 
			$stmt = $auth_user->runQuery("SELECT * FROM LocalisationServices WHERE batiment=:batiment AND aile=:aile AND etage=:etage");
			$stmt->execute(array('batiment'=>$text_batiment, 'aile'=>$text_aile,'etage'=>$text_etage));
			$row=$stmt->fetch(PDO::FETCH_ASSOC);
			
			if($row['batiment']==$text_batiment and $row['aile']==$text_aile and $row['etage']==$text_etage)  
			{
				$BddidLocalisation=$row['idLocalisation'];
				$ajoutService = $auth_user->runQuery("UPDATE Services 
																SET 
																nomService=:text_nomService,
															   telephone=:text_telephone, 
																mail=:text_mail,
																horaire_ouverture=:text_ouverture,
																horaire_fermeture=:text_fermeture,
																LocalisationServicesidLocalisation=:BddidLocalisation
																WHERE nomService=:serviceModifier");
				$ajoutService->execute(array('text_nomService'=>$text_nomService,
											'text_telephone'=>$text_telephone,
											'text_mail'=>$text_mail,
											'text_ouverture'=>$text_ouverture,
											'text_fermeture'=>$text_fermeture,
											'BddidLocalisation'=>$BddidLocalisation,
											'serviceModifier'=>$_SESSION['serviceModifier']));
			}
			else
			{	
				//Ajout de la localisation 
				$ajoutLocalisation = $auth_user->runQuery("INSERT INTO LocalisationServices (batiment, aile, etage) 
															VALUES (:batiment, :aile, :etage) ");  // preparation de la requete SQL
				$ajoutLocalisation->execute(array('batiment'=>$text_batiment,
											'aile'=>$text_aile,
											'etage'=>$text_etage));   // execution de la requete SQL, ajout de la localisation du service 
				$stmt = $auth_user->runQuery("SELECT MAX(idLocalisation) FROM LocalisationServices");  // recuperation du dernier id rentrée
				$stmt->execute(); // recuperation du dernier id rentrée
				$BddidLocalisation = $stmt->fetch(PDO::FETCH_ASSOC)["MAX(idLocalisation)"]; // recuperation du dernier id localisation entrée dans la BDD
				
				$ajoutService = $auth_user->runQuery("UPDATE Services 
																SET 
																nomService=:text_nomService,
															   telephone=:text_telephone, 
																mail=:text_mail,
																horaire_ouverture=:text_ouverture,
																horaire_fermeture=:text_fermeture,
																LocalisationServicesidLocalisation=:BddidLocalisation
																WHERE nomService=:serviceModifier");
				$ajoutService->execute(array('text_nomService'=>$text_nomService,
											'text_telephone'=>$text_telephone,
											'text_mail'=>$text_mail,
											'text_ouverture'=>$text_ouverture,
											'text_fermeture'=>$text_fermeture,
											'BddidLocalisation'=>$BddidLocalisation,
											'serviceModifier'=>$_SESSION['serviceModifier']));   // execution de la requete SQL et ajout du service dans la base 
			} 
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();
		}
		$auth_user->redirect('ServiceCreer.php?Valide'); // une fois l ensemble des messages affiché, 
	}

}
?>	

<!DOCTYPE html PUBLIC >
<html>
	<head>
		<link rel="stylesheet" href=Style.css">
		<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
		<title>Modifier le service</title>
	</head>
	<body>
		<?php // affichage
			If (!array_key_exists("serviceModifier",$_SESSION )) 
			{
				include ('../Formulaires/RechercheService.php');; // recherche le service
			}
			else
			{
				$req_service = $auth_user->runQuery("SELECT * 
										FROM Services
										WHERE  nomService = :nomService");
				
				$req_service->execute(array("nomService"=>$_SESSION['serviceModifier']));
				$serviceInfo=$req_service -> fetch(PDO::FETCH_ASSOC);
				include ('../Formulaires/ServiceModifier.php');; // recherche patient existe pas (redirection fiche patient)
			}
		?>
	</body>

</html>