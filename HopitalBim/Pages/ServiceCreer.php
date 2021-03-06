<?php
	include ('../Config/Menupage.php');
	if ($_SESSION["idEmploye"] != 'admin00')
	{
	$auth_user->redirect('../Pageprincipale.php');
	}

	if(isset($_POST['btn-valider']))
	{
 // Recuperation des champs entrés dans le formulaire : 
	// recuperation des information relatif à la table Services
		$text_nomService = ucfirst(str_replace(' ','',$_POST['text_nomService']));	
		$text_telephone = str_replace(' ','',$_POST['text_telephone']);
		
		$text_mail = $text_nomService."@hopitalbim.fr";   // l'adresse mail sera toujours = au nom de service+@hotpitalbim.fr
		$text_ouverture = date('H:i', strtotime($_POST['text_ouverture']));
		$text_fermeture = date('H:i', strtotime($_POST['text_fermeture'])); 
	// recuperation des information relatif à la table LocalisationServices
		$text_batiment = $_POST['text_batiment'];	
		$text_etage = $_POST['text_etage'];	
		$text_aile = $_POST['text_aile'];	
	// TEST si le service est deja present : 
		$stmt = $auth_user->runQuery("SELECT nomService FROM Services WHERE nomService=:nomService ");
		$stmt->execute(array('nomService'=>$text_nomService));
		$rechercheService=$stmt->fetch(PDO::FETCH_ASSOC);
	// Apres avoir realisé une requete pour rechercher les services, on va tester si celui est present dans la bdd
		if($text_nomService=="")	{
			$error[] = "Il faut ajouter un nom de service ! "; }
		else if((preg_match('/[0-9]+/',$text_telephone) == 0)or ($text_telephone==""))	 {
			$error[] = "Veuillez entrer le numéro de téléphone du service !"; }
		else if ($text_ouverture == $text_fermeture) {
			$error[] = "Veuillez entrer des horaires d'ouverture et de fermeture valide !"; }
		else if ($text_ouverture > $text_fermeture) {
			$error[] = "L'heure d'ouverture ne peut pas excéder l'heure de fermeture !"; }
		else if ($rechercheService['nomService']==$text_nomService) {
			$error[] = "Le service est déjà présent dans la base de données !"; }
		else
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
					$ajoutService = $auth_user->runQuery("INSERT INTO Services (nomService, telephone, mail, horaire_ouverture, horaire_fermeture, LocalisationServicesidLocalisation) 
							VALUES (:nomService, :telephone, :mail, :horaire_ouverture, :horaire_fermeture, :LocalisationServicesidLocalisation) ");
					$ajoutService->execute(array('nomService'=>$text_nomService,
												'telephone'=>$text_telephone,
												'mail'=>$text_mail,
												'horaire_ouverture'=>$text_ouverture,
												'horaire_fermeture'=>$text_fermeture,
												'LocalisationServicesidLocalisation'=>$BddidLocalisation));
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
					
					$ajoutService = $auth_user->runQuery("INSERT INTO Services (nomService, telephone, mail, horaire_ouverture, horaire_fermeture, LocalisationServicesidLocalisation) 
							VALUES (:nomService, :telephone, :mail, :horaire_ouverture, :horaire_fermeture, :LocalisationServicesidLocalisation) "); // preparation de la requete SQL
					$ajoutService->execute(array('nomService'=>$text_nomService,
												'telephone'=>$text_telephone,
												'mail'=>$text_mail,
												'horaire_ouverture'=>$text_ouverture,
												'horaire_fermeture'=>$text_fermeture,
												'LocalisationServicesidLocalisation'=>$BddidLocalisation));   // execution de la requete SQL et ajout du service dans la base 
				} 
			}
			catch(PDOException $e)
			{
				echo $e->getMessage();
			}
			$auth_user->redirect('ServiceCreer.php?Valide'); // une fois l ensemble des messages affichés 
		}
	}


?>

<!DOCTYPE html PUBLIC >
<html>
	<head>
		<title> Nouveau Service </title> <!-- Titre de l'onglet -->
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" href="../Config/Style.css" type="text/css">
		<link href="https://fonts.googleapis.com/css?family=Josefin+Slab" rel="stylesheet">
	</head>

	<body>
		<?php include ('../Formulaires/Formulaire_ServiceCreer.php'); ?>
		<?php include ('../Config/Footer.php');  ?>

	</body>
</html>
