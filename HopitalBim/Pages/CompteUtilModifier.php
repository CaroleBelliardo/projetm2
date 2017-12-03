<?php

	include ('../Config/Menupage.php');
	$lien ='CompteUtilModifier.php';


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
			If (!array_key_exists("utilisateurModifier",$_SESSION )) 
			{
				include ('../Formulaires/RechercheUtilisateur.php');; // recherche le service
			}
			else
			{
				$req_utilisateur = $auth_user->runQuery("SELECT * 
														FROM Employes Join Adresses JOIN Villes
														WHERE Employes.AdressesidAdresse = Adresses.idAdresse
														AND Adresses.VillesidVilles = Villes.idVilles
														AND  CompteUtilisateursidEmploye = :utilisateur");
				
				$req_utilisateur->execute(array("utilisateur"=>$_SESSION['utilisateurModifier']));
				$utilisateurInfo=$req_utilisateur -> fetch(PDO::FETCH_ASSOC);
				include ('../Formulaires/CompteUtilModifier.php');; // recherche patient existe pas (redirection fiche patient)
			}
		?>
	</body>

</html>