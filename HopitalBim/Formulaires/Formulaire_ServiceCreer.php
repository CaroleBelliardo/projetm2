<div class="containerFormu">
    	
        <form method="post" class="form-signin">

            <h2 class="form-signin-heading">Ajouter un nouveau service</h2> <hr />

            <?php
			if(isset($error))
			{
			 	foreach($error as $error)
			 	{
					 ?>
                     <div id="error"> &nbsp; <?php echo $error; ?> </div> <!-- Alert alert-danger-->

                     <?php
				}
			}
			else if(isset($_GET['Valide']))
			{
			?>
                 <div id="valide"> <!-- Alert alert-info-->
                      Service ajouté avec succés ! <a href='../Pageprincipale.php'> Page principale </a>
                 </div>
            <?php
			}
			?>
			
			<p><i>Complétez le formulaire. Les champs marqués par </i><em>*</em> sont <em>obligatoires.</em></p>

			<div class="form-group">
			<fieldset>
			<legend> Service </legend> <!-- Titre du fieldset --> 

				<label for="text_nomService">Nom du service <em>* </em> </label>
				<input type="text" class="form-control" name="text_nomService" pattern="[A-Za-z]{1-20}" title="Majuscule en première lettre"        placeholder=" Service" value="<?php if(isset($error)){echo $text_nomService;}?>" /><br>

				<label for="text_telephone">Téléphone <em>*</em></label>
				<input type="tel" class="form-control" name="text_telephone" pattern="[0-9]{1-15}" title="Veuillez rentrer un n° de téléphone correct"    placeholder=" 06xxxxxxxx " value="<?php if(isset($error)){echo $text_telephone;}?>" /><br>

				<label for="text_ouverture">Horaire d'ouverture<em>*</em></label>
				<input type="time" step="900" class="form-control" name="text_ouverture" value="<?php if(isset($error)){echo $text_ouverture;}?>" /><br>

				<label for="text_fermeture">Fermeture <em>* </em> </label>
				<input type="time" step="900" class="form-control" name="text_fermeture" value="<?php if(isset($error)){echo $text_fermeture;}?>" /><br>

			</fieldset> <br>
			
			<fieldset>
			<legend> Localisation </legend> <!-- Titre du fieldset --> 

				<label for="text_batiment">Bâtiment </label>  
				<select name="text_batiment">
					<option value="A">A</option>
					<option value="B">B</option>
					<option value="C">C</option>
					<option value="D">D</option>
					<option value="E">E</option>
					<option value="F">F</option>
				</select><br> 

				<label for="text_etage">Étage </label>  
				<select name="text_etage"> 
					<option value="0">RDC</option>
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="-1">-1</option>
				</select><br>

				<label for="text_aile">Aile </label>  
				<select name="text_aile">
					<option value="a">a</option>
					<option value="b">b</option>
					<option value="c">c</option>
					<option value="d">d</option>
					<option value="e">e</option>
					<option value="f">f</option>
				</select>
			
			</fieldset> <br>
			</div> <!-- form-group // Formulaire principal --> 

            <div class="form-group">
            	<button type="submit" class="btn btn-primary" name="btn-valider">Valider</button>
            </div> <!-- form-group // Bouton Valider -->

        </form> <!-- form-signin -->

</div> <!-- containerFormu -->


<?php quitter1() ?>

