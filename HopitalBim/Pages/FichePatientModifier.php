<?php
/*  Description Global du fichier :
	- Permet de modifier un patient dans la base de donnée grace a un formulaire
*/
	include ('../Config/Menupage.php');
	$lien ='FichePatientModifier.php';

	if(isset($_POST['btn-modifier']))
	{
		/* Recuperation de l'ensemble des champs du formulaire avec gestion selon le
	  type recueilli. Chaque variable qui commence par "text_" contient les informations
	  ou "input" rentrées par l'utilisateur. Ces informations seront ajoutées dans
	  la base de donnée.
	   - "trim()" permet de retirer un caractere.
	 	- "ucfirst()" permet de mettre en capital la premiere lettre
	 	- "striptag()" permet de retirer les balises html
	 	- "preg_replace("/[^0-9]/", "",$var)" permet de retirer les caracteres [a-zA-Z]
	 */
	 	// Recuperation de l'ensemble des input de l'utilisateur
		$patientModif = $_SESSION['patient'];
		$text_departement = trim($_POST['text_departement'], ' ' );
		$text_pays = ucfirst(trim($_POST['text_pays'], ' '))	;

		$text_ville = ucfirst(trim($_POST['text_ville'], ' '))	;
		$text_codepostal = str_replace(' ','',$_POST['text_codepostal']);

		$text_numero = trim($_POST['text_numero'], ' ' );
		$text_rue = ucfirst(trim($_POST['text_rue'], ' '))	;

		$text_numSS = trim($_POST['text_numSS'], ' ' );
		$text_nom =  ucfirst(trim($_POST['text_nom'], ' '))	;
		$text_prenom = ucfirst(trim($_POST['text_prenom'], ' '))	;
		$text_dateNaissance = strip_tags($_POST['text_dateNaissance']);

		$text_telephone = trim($_POST['text_telephone'], ' ' );
		$text_mail = str_replace(' ','',$_POST['text_mail']);
		$text_sexe = strip_tags($_POST['text_sexe']);
		$text_taille = preg_replace("/[^0-9]/", "",trim($_POST['text_taille'], ' '));
		$text_poids = preg_replace("/[^0-9]/", "",trim($_POST['text_poids'], ' '));
		$text_commentaires = strip_tags($_POST['text_commentaires']);

		// Requete afin de verifier si le numéro de sécurité sociale entré est deja present dans la base de donnée
		$stmt = $auth_user->runQuery("SELECT numSS FROM Patients WHERE numSS=:text_numSS ");
		$stmt->execute(array('text_numSS'=>$text_numSS));
		$row=$stmt->fetch(PDO::FETCH_ASSOC);


		/* Realisation d'un ensemble de tests. La gestion d erreur est faite pas a ftp_pas
		 - Verification que le numero de securité sociale vaut 15 caractere alphanumériques
		 - Le nom est prenom peuvent pas etre vide ou contenir des chiffes
		 - La date de naissance et la rue peuvent pas etre nulle
		 - le code postal peut pas etre plus long que 5 characteres, 3 pour le numero departemental, 25 pour le pays. Ils ne peuvent pas etre nulle
		 - le nom du pays doit pas contenir des chiffres
		 - Le numero de securité sociale du nouveau patient ne peut pas être identique a un patient deja enregistré
		*/
		if($text_numSS==""  or (is_numeric($text_numSS)==FALSE ) or (strlen($text_numSS) < 15 ) or (strlen($text_numSS) > 15 ))	{
			$error[] = "Vérifiez que le numéro de sécurité sociale est correct !"; }
		else if((preg_match('/[0-9]+/',$text_nom) == 1)or ($text_nom=="")) {// string only contain the a to z , A to Z,
			$error[] = "Veuillez entrer un nom uniquement composé de lettres !";}
		else if((preg_match('/[0-9]+/',$text_prenom) == 1)or ($text_prenom=="")) {// string only contain the a to z , A to Z,
			$error[] = "Veuillez entrer un prénom uniquement composé de lettres !";}
		else if(($_POST['text_dateNaissance'])=="")	{
			$error[] = "Veuillez respecter le format jj/mm/aaaa !"; }
		else if(strlen($text_taille) > 3) 	{
			$error[] = "Il faut entrer une taille valide !"; }
		else if(strlen($text_poids) > 3) 	{
			$error[] = "Il faut entrer une taille valide !"; }
		else if((preg_match('/[0-9]+/',$text_numero) == 0) or ($text_numero=="") )	{
			$error[] = "Veuillez entrer un numéro de rue !"; }
		else if($text_rue=="" )	{
			$error[] = "Veuillez entrer un nom de rue valide !"; }
		else if($text_ville=="" )	{
			$error[] = "Veuillez entrer le nom d'une ville valide !"; }
		else if(strlen($text_codepostal) > 5)	{
			$error[] = "Veuillez entrer un code postal valide !"; }
		else if(strlen($text_departement) > 3) 	{
			$error[] = "Veuillez entrer un numéro de département de maximum 3 caractères alphanumériques (entrez 99 si le patient réside à l'étranger) !"; }
		else if ((preg_match('/[0-9]+/',$text_pays) == 1)or ($text_pays=="") or (strlen($text_pays) > 25))	{
			$error[] = "Veuillez entrer un pays (caractères numériques non acceptés)!"; }
		// TEST SI NUMSS deja present
		else if ($row['numSS']=="" or $row['numSS']==$patientModif)
		{
			try
			{// Apres gestion des erreurs, on ajoute les informations du formulaires dans la base de donnée
				// On verifie si son adresse est presente dans la table Villes,
				$_SESSION['patient']=$text_numSS ;
				// On verifie si son adresse est presente dans la table Villes,
				$stmt = $auth_user->runQuery("SELECT * FROM Villes
											WHERE codepostal=:text_codepostal AND nomVilles=:text_ville
											AND departement=:text_departement AND pays=:text_pays");
				$stmt->execute(array('text_codepostal'=>$text_codepostal, 'text_ville'=>$text_ville, 'text_departement'=>$text_departement, 'text_pays'=>$text_pays));
				$row=$stmt->fetch(PDO::FETCH_ASSOC);
				$BDDidVilles=$row['idVilles'];
				if ($row['codepostal']==$text_codepostal and  $row['nomVilles']==$text_ville and $row['departement']==$text_departement and $row['pays']==$text_pays)
				{//On verifie si son adresse est presente dans la table Adresses,
					$stmt = $auth_user->runQuery("SELECT * FROM Adresses
											WHERE numero=:text_numero AND rue=:text_rue AND VillesidVilles=:BDDidVilles");
					$stmt->execute(array('text_numero'=>$text_numero, 'text_rue'=>$text_rue, 'BDDidVilles'=>$BDDidVilles));
					$row=$stmt->fetch(PDO::FETCH_ASSOC);
					$BDDidAdresse=$row['idAdresse'];
					if ($row['numero']==$text_numero and  $row['rue']==$text_rue and $row['VillesidVilles']==$BDDidVilles )
					{	// Si l'ensemble des informations relatives a l adresse du patient sont deja present, alors seul les informations relatives au patient sont ajoutées
						$ajoutpatient = $auth_user->runQuery("UPDATE Patients
																	SET
																	numSS=:text_numSS,
																    nom=:text_nom,
																    prenom=:text_prenom,
																    dateNaissance=:text_dateNaissance,
																    telephone=:text_telephone,
																    mail=:text_mail,
																    sexe=:text_sexe,
																    taille_cm=:text_taille,
																    poids_kg=:text_poids,
																    commentaires=:text_commentaires,
																    AdressesidAdresse=:BDDidAdresse
																    WHERE numSS =:patientModif");

						$ajoutpatient->execute(array('text_numSS'=>$text_numSS,
													 'text_nom'=>$text_nom,
													 'text_prenom'=>$text_prenom,
													 'text_dateNaissance'=>$text_dateNaissance,
													 'text_telephone'=>$text_telephone,
													 'text_mail'=>$text_mail,
													 'text_sexe'=>$text_sexe,
													 'text_taille'=>$text_taille,
													 'text_poids'=>$text_poids,
													 'text_commentaires'=>$text_commentaires,
													 'BDDidAdresse'=>$BDDidAdresse,
													 'patientModif'=>$patientModif));

						// Ajout des informations du patient et redirection
						$auth_user->redirect('FichePatientCreer.php?Valide');
					}
					else
					{

						//  Ajout des informations dans la table adresse
						$stmtAdresses = $auth_user->runQuery("INSERT INTO Adresses (numero, rue, VillesidVilles)
												VALUES (:text_numero, :text_rue, :BDDidVilles )");

						$stmtAdresses->execute(array('text_numero'=>$text_numero,
													 'text_rue'=>$text_rue,
													 'BDDidVilles'=>$BDDidVilles));
						$stmt = $auth_user->runQuery("SELECT * FROM Adresses
											WHERE numero=:text_numero AND rue=:text_rue AND VillesidVilles=:BDDidVilles");
						$stmt->execute(array('text_numero'=>$text_numero,
											 'text_rue'=>$text_rue,
											 'BDDidVilles'=>$BDDidVilles));
						$row=$stmt->fetch(PDO::FETCH_ASSOC);
						$BDDidAdresse=$row['idAdresse'];
						// -- Ajout Patient
						$ajoutpatient = $auth_user->runQuery("UPDATE Patients
																	SET
																	numSS=:text_numSS,
																   nom=:text_nom,
																   prenom=:text_prenom,
																   dateNaissance=:text_dateNaissance,
																   telephone=:text_telephone,
																   mail=:text_mail,
																   sexe=:text_sexe,
																   taille_cm=:text_taille,
																   poids_kg=:text_poids,
																   commentaires=:text_commentaires,
																   AdressesidAdresse=:BDDidAdresse
																   WHERE numSS =:patientModif");

						$ajoutpatient->execute(array('text_numSS'=>$text_numSS,
													 'text_nom'=>$text_nom,
													 'text_prenom'=>$text_prenom,
													 'text_dateNaissance'=>$text_dateNaissance,
													 'text_telephone'=>$text_telephone,
													 'text_mail'=>$text_mail,
													 'text_sexe'=>$text_sexe,
													 'text_taille'=>$text_taille,
													 'text_poids'=>$text_poids,
													 'text_commentaires'=>$text_commentaires,
													 'BDDidAdresse'=>$BDDidAdresse,
													 'patientModif'=>$patientModif));
						//-------------
						$auth_user->redirect('FichePatientCreer.php?Valide');
					}


				}
				else
				{
					$stmtville = $auth_user->conn->prepare("INSERT INTO Villes ( codepostal, nomVilles, departement, pays)
													VALUES ( :text_codepostal, :text_ville, :text_departement, :text_pays)");
					$stmtville->execute(array('text_codepostal'=>$text_codepostal,
											  'text_ville'=>$text_ville,
											  'text_departement'=>$text_departement,
											  'text_pays'=>$text_pays));
					$stmt = $auth_user->runQuery("SELECT * FROM Villes
											WHERE codepostal=:text_codepostal AND nomVilles=:text_ville
											AND departement=:text_departement AND pays=:text_pays");
					$stmt->execute(array('text_codepostal'=>$text_codepostal, 'text_ville'=>$text_ville, 'text_departement'=>$text_departement, 'text_pays'=>$text_pays));
					$row=$stmt->fetch(PDO::FETCH_ASSOC);
					$BDDidVilles=$row['idVilles'];
					// -- Ajout dans adresse
					$stmtAdresses = $auth_user->runQuery("INSERT INTO Adresses (numero, rue, VillesidVilles)
													VALUES (:text_numero, :text_rue, :BDDidVilles )");

					$stmtAdresses->execute(array('text_numero'=>$text_numero,
												   'text_rue'=>$text_rue,
												   'BDDidVilles'=>$BDDidVilles));
					$stmt = $auth_user->runQuery("SELECT * FROM Adresses
												WHERE numero=:text_numero AND rue=:text_rue AND VillesidVilles=:BDDidVilles");
					$stmt->execute(array('text_numero'=>$text_numero, 'text_rue'=>$text_rue, 'BDDidVilles'=>$BDDidVilles));
					$row=$stmt->fetch(PDO::FETCH_ASSOC);
					$BDDidAdresse=$row['idAdresse'];
					// -- Ajout Patient
					$ajoutpatient = $auth_user->runQuery("UPDATE Patients
															SET
															numSS=:text_numSS,
															nom=:text_nom,
															prenom=:text_prenom,
															dateNaissance=:text_dateNaissance,
															telephone=:text_telephone,
															mail=:text_mail,
															sexe=:text_sexe,
															taille_cm=:text_taille,
															poids_kg=:text_poids,
															commentaires=:text_commentaires,
															AdressesidAdresse=:BDDidAdresse
															WHERE numSS =:patientModif");

					$ajoutpatient->execute(array('text_numSS'=>$text_numSS,
													'text_nom'=>$text_nom,
													'text_prenom'=>$text_prenom,
													'text_dateNaissance'=>$text_dateNaissance,
													'text_telephone'=>$text_telephone,
													'text_mail'=>$text_mail,
													'text_sexe'=>$text_sexe,
													'text_taille'=>$text_taille,
													'text_poids'=>$text_poids,
													'text_commentaires'=>$text_commentaires,
													'BDDidAdresse'=>$BDDidAdresse,
													'patientModif'=>$patientModif));
					//-------------
					$auth_user->redirect('FichePatientCreer.php?Valide');
				}
			}
			catch(PDOException $e)
			{
				echo $e->getMessage();
			}
		}else {
			$error[] = "Vous ne pouvez pas modifier le numéro de sécurité social de ce patient !";
		}
	}

	if(isset($_POST['redirection']))
	{
	$auth_user->redirect('RDVDemande.php');
	}

?>

<!DOCTYPE html PUBLIC >
<html>
	<head>
		<title> Modifier Patient </title> <!-- Titre de l'onglet -->
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" href="../Config/Style.css" type="text/css">
		<link href="https://fonts.googleapis.com/css?family=Josefin+Slab" rel="stylesheet">
	</head>

	<body>
		<?php // affichage
			If (!array_key_exists("patient",$_SESSION ))
			{
				include ('../Formulaires/Formulaire_RecherchePatient.php');; // recherche patient -> si n'existe pas : redirection fiche patient !
			}
			else
			{
				$req_patient = $auth_user->runQuery("SELECT *
										FROM Patients Join Adresses JOIN Villes
										WHERE Patients.AdressesidAdresse = Adresses.idAdresse
										AND Adresses.VillesidVilles = Villes.idVilles
										AND Patients.numSS = :numSS");

				$req_patient->execute(array("numSS"=>$_SESSION['patient']));
				$patientInfo=$req_patient -> fetch(PDO::FETCH_ASSOC);
				include ('../Formulaires/Formulaire_FichePatientModifier.php');; // recherche patient -> si existe : redirection fiche patient !
			}
		?>
	</body>
</html>
