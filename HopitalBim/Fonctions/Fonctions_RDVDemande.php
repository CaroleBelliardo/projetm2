<?php 	// Fonctions
    
	function ProchaineHeureArrondie() // Recuperer l'heure actuelle et de arrondie l'heure à la quinzaine 
    { 
        $current_time = strtotime(date('H:i')); // recupere l'heure actuelle sous format hh:mm
        $frac = 900;                            // pour definir le creneaux horaire :  15 min x 60 sec = 900 
        $r = $current_time % $frac;             // calcul le nombre de minutes necessaire a ajouter.
        $heureArrondie = date('H:i',($current_time + ($frac-$r))); // calcule combien de minute il faut ajouter pour passer au prochain creneaux de 15 mins
        return ($heureArrondie); // retourne la prochaine heure arrondie
    }

    function heurePlus15($h,$temps) // prend en entrée une string h:m et renvoye l'heure + 15minutes
	{
		$heure = strtotime($h);
		$heurePlus15 =date("H:i", strtotime( $temps,$heure));
		return ($heurePlus15);
	}

    function prochainCreneauxDispo($auth_user,$idInterv) // recherche le dernier creneau occupé ou le premier creneau annulé 
    {
	$req_infoDateHeure = $auth_user->runQuery(" SELECT *
                FROM  (
					(SELECT dateR1 as dateR, heureR1 as heureR, statutR1 as statutR, idR1 as idR
					FROM  (
						(SELECT date_rdv as dateR1, heure_rdv as heureR1, CreneauxInterventions.statut as statutR1, CreneauxInterventions.id_rdv as idR1       
						FROM CreneauxInterventions JOIN Interventions  JOIN Services 
						WHERE  CreneauxInterventions.InterventionsidIntervention = Interventions.idIntervention
						AND Interventions.ServicesnomService = Services.nomService
						AND date_rdv = CURDATE() 
						AND heure_rdv > CURRENT_TIMESTAMP()
						AND InterventionsidIntervention = :idIntervention
						AND CreneauxInterventions.statut != 'a'
						AND heure_rdv >= (SELECT horaire_ouverture FROM Interventions  JOIN Services WHERE idIntervention = :idIntervention AND Interventions.ServicesnomService = Services.nomService)
						AND heure_rdv <= (SELECT horaire_fermeture FROM Interventions  JOIN Services WHERE idIntervention = :idIntervention  AND Interventions.ServicesnomService = Services.nomService)
						ORDER BY  date_rdv DESC, heure_rdv DESC LIMIT 1 
) 
					UNION
						(SELECT  date_rdv as dateR1, heure_rdv  as heureR1, CreneauxInterventions.statut as statutR1, CreneauxInterventions.id_rdv as idR1        
						FROM CreneauxInterventions JOIN Interventions  JOIN Services 
						WHERE  CreneauxInterventions.InterventionsidIntervention = Interventions.idIntervention
						AND Interventions.ServicesnomService = Services.nomService
						AND date_rdv > CURDATE() 
						AND InterventionsidIntervention = :idIntervention
						AND CreneauxInterventions.statut != 'a'
						AND heure_rdv >= (SELECT horaire_ouverture FROM Interventions  JOIN Services WHERE idIntervention = :idIntervention AND Interventions.ServicesnomService = Services.nomService)
						AND heure_rdv <= (SELECT horaire_fermeture FROM Interventions  JOIN Services WHERE idIntervention = :idIntervention  AND Interventions.ServicesnomService = Services.nomService)
                			ORDER BY  date_rdv DESC, heure_rdv DESC LIMIT 1 
) ORDER BY  dateR1 DESC, heureR1 DESC LIMIT 1  )as d  )
                UNION
					(SELECT date_rdv as dateR, heure_rdv  as heureR, CreneauxInterventions.statut as statutR, CreneauxInterventions.id_rdv as idR      
					FROM CreneauxInterventions JOIN Interventions  JOIN Services 
					WHERE CreneauxInterventions.InterventionsidIntervention = Interventions.idIntervention
					AND Interventions.ServicesnomService = Services.nomService
					AND date_rdv = CURDATE()
					AND heure_rdv > CURRENT_TIMESTAMP() 
					AND CreneauxInterventions.statut = 'a'
					AND InterventionsidIntervention = :idIntervention
					AND heure_rdv >= (SELECT horaire_ouverture FROM Interventions  JOIN Services WHERE idIntervention = :idIntervention AND Interventions.ServicesnomService = Services.nomService)
					AND heure_rdv <= (SELECT horaire_fermeture FROM Interventions  JOIN Services WHERE idIntervention = :idIntervention  AND Interventions.ServicesnomService = Services.nomService)
					ORDER BY  date_rdv ASC , heure_rdv ASC LIMIT 1                
					)
                UNION
					(SELECT date_rdv as dateR, heure_rdv  as heureR, CreneauxInterventions.statut as statutR, CreneauxInterventions.id_rdv as idR
					FROM CreneauxInterventions JOIN Interventions  JOIN Services 
					WHERE CreneauxInterventions.InterventionsidIntervention = Interventions.idIntervention
					AND Interventions.ServicesnomService = Services.nomService
					AND date_rdv > CURDATE()
					AND CreneauxInterventions.statut = 'a'
					AND InterventionsidIntervention = :idIntervention
					AND heure_rdv >= (SELECT horaire_ouverture FROM Interventions  JOIN Services WHERE idIntervention = :idIntervention AND Interventions.ServicesnomService = Services.nomService)
					AND heure_rdv <= (SELECT horaire_fermeture FROM Interventions  JOIN Services WHERE idIntervention = :idIntervention  AND Interventions.ServicesnomService = Services.nomService)
               		ORDER BY  date_rdv ASC , heure_rdv ASC LIMIT 1              
	 ) 
			) as dd WHERE dateR IS NOT NULL ORDER BY dateR ASC, heureR ASC  LIMIT 1 
		");
		$req_infoDateHeure->execute(array('idIntervention' => $idInterv)); // modifier variables
		$a_infoDateHeure = reqToArrayPlusAttASSO($req_infoDateHeure); // retourne : [ MIN(dateR), MIN(heureR), statutR, idR ] heure = dernier rdv prevu ou premier rdv annulé 
		$req_infoDateHeure->closeCursor();
        return ($a_infoDateHeure);
    }

	
    function CreneauxUrgent($auth_user, $nivUrg, $idInterv) // recherche le dernier creneau occupé ou le premier creneau annulé avec nivUrgence 
    {
        $req_infoDateHeureUrg = $auth_user->runQuery("SELECT *
FROM  
(
	(
		SELECT dateR1 as dateR, heureR1 as heureR, statutR1 as statutR, idR1 as idR, niveauUrgenceR1 as niveauUrgenceR
		FROM  
		(
		
			(
				SELECT date_rdv as dateR1, heure_rdv as heureR1, CreneauxInterventions.statut as statutR1, CreneauxInterventions.id_rdv as idR1  , niveauUrgence as niveauUrgenceR1     
				FROM CreneauxInterventions JOIN Interventions  JOIN Services 
				WHERE  CreneauxInterventions.InterventionsidIntervention = Interventions.idIntervention
				AND Interventions.ServicesnomService = Services.nomService
				AND date_rdv = CURDATE() 
				AND heure_rdv > CURRENT_TIMESTAMP()
				AND InterventionsidIntervention = :idIntervention
				AND CreneauxInterventions.statut != 'a'
				AND CreneauxInterventions.niveauUrgence >= :niveauUrgence
				 ORDER BY date_rdv DESC, heure_rdv DESC LIMIT 1
			)
			UNION
			(
				SELECT  date_rdv as dateR1, heure_rdv  as heureR1, CreneauxInterventions.statut as statutR1, CreneauxInterventions.id_rdv as idR1, niveauUrgence as niveauUrgenceR1  
				FROM  CreneauxInterventions JOIN Interventions  JOIN Services 
				WHERE  CreneauxInterventions.InterventionsidIntervention = Interventions.idIntervention
				AND Interventions.ServicesnomService = Services.nomService
				AND date_rdv > CURDATE() 
				AND InterventionsidIntervention = :idIntervention
				AND CreneauxInterventions.statut != 'a'
				AND CreneauxInterventions.niveauUrgence >= :niveauUrgence
				ORDER BY date_rdv DESC, heure_rdv DESC LIMIT 1
			) 
				ORDER BY  dateR1 DESC, heureR1 DESC LIMIT 1  
		) as d  
	)
	UNION
	(
		SELECT date_rdv as dateR, heure_rdv  as heureR, CreneauxInterventions.statut as statutR, CreneauxInterventions.id_rdv as idR, niveauUrgence as niveauUrgenceR      
		FROM CreneauxInterventions JOIN Interventions  JOIN Services 
		WHERE CreneauxInterventions.InterventionsidIntervention = Interventions.idIntervention
		AND Interventions.ServicesnomService = Services.nomService
		AND date_rdv = CURDATE() 
		AND heure_rdv > CURRENT_TIMESTAMP() 
		AND CreneauxInterventions.statut = 'a'
		AND InterventionsidIntervention = :idIntervention
		ORDER BY  date_rdv ASC , heure_rdv LIMIT 1
	)
	UNION 
	(
		SELECT date_rdv as dateR, heure_rdv  as heureR, CreneauxInterventions.statut as statutR, CreneauxInterventions.id_rdv as idR, niveauUrgence as niveauUrgenceR      
		FROM CreneauxInterventions JOIN Interventions  JOIN Services 
		WHERE CreneauxInterventions.InterventionsidIntervention = Interventions.idIntervention
		AND Interventions.ServicesnomService = Services.nomService
		AND date_rdv > CURDATE() 
		AND CreneauxInterventions.statut = 'a'
		AND InterventionsidIntervention = :idIntervention
		ORDER BY  date_rdv ASC , heure_rdv LIMIT 1
	)
) as dd WHERE dateR IS NOT NULL ORDER BY dateR ASC, heureR ASC");
        $req_infoDateHeureUrg->execute(array( 'niveauUrgence'=> $nivUrg,
											 'idIntervention'=>$idInterv)); // modifier variables
		$a_infoDateHeureUrg = reqToArrayPlusAttASSO($req_infoDateHeureUrg); // retourne : [ MIN(dateR), MIN(heureR), statutR, idR ] heure = dernier rdv prevu ou premier rdv annulé 
		$req_infoDateHeureUrg->closeCursor();
        return ($a_infoDateHeureUrg);
    }
  
?>