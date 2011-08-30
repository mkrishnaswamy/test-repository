 <?php    
/* ----------------------------------------------------------------------
 * support/import/projects/pier21/import_oral_histories.php :   
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2010 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 *
 * This source code is free and modifiable under the terms of 
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 *
 * ----------------------------------------------------------------------
 */
	require_once("/usr2/ca/setup.php");
	$_SERVER['HTTP_HOST'] = 'ca.sept11mm.org';

	require_once(__CA_LIB_DIR__.'/core/Db.php');
	require_once(__CA_MODELS_DIR__.'/ca_locales.php');
	require_once(__CA_MODELS_DIR__.'/ca_objects.php');
	require_once(__CA_MODELS_DIR__.'/ca_object_lots.php');
	require_once(__CA_MODELS_DIR__.'/ca_object_lot_labels.php');
	require_once(__CA_MODELS_DIR__.'/ca_entities.php');
	require_once(__CA_MODELS_DIR__.'/ca_entity_labels.php'); 
	require_once(__CA_MODELS_DIR__.'/ca_occurrences.php');
	require_once(__CA_MODELS_DIR__.'/ca_occurrence_labels.php');
	require_once(__CA_MODELS_DIR__.'/ca_storage_locations.php');
	require_once(__CA_MODELS_DIR__.'/ca_storage_location_labels.php');
	require_once(__CA_MODELS_DIR__.'/ca_relationship_types.php');
	require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');
	require_once(__CA_LIB_DIR__.'/ca/Utils/DataMigrationUtils.php');


function addobject($lot_primary_key, $lot_num, $filename, $ca_id, $vs_path, $object_num, $full_name)
{


	// Muscoll Test

//	$object_num = 0; // to be set to the last value of 4 part id of the object in the lot.

	$provenance = "Content Transferred from Voices of September 11th Living Memorial Project";
	$right_notes = "National September 11 Memorial & Museum owns perpetual, irrevocable license for all purposes.";
	
	//$entity_type_id = 41; //creator

       //	$entity_id=11400;
	$collection_type_id = 34; //is part of
	//$vs_path = "/usr_test/scripts/media/androidmarker.png";
	//$doc_file = "/usr_test/scripts/forms/books.txt";


	$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'en_US');
	
	$t_locale = new ca_locales();
	$g_ui_locale = 'en_US';
	$pn_locale_id = $t_locale->loadLocaleByCode('en_US');		// default locale_id
        
        //description
        $desc = "Digital Content Associated with $full_name";

	// check ca_lists and find the list_id for appropriate one from ca_list_items 
	$t_list= new ca_lists();
	$vn_rep_type_id	= $t_list->getItemIDFromList('object_representation_types', 'front');
	$dig_img_type_id = $t_list->getItemIDFromList('object_types', 'image_digital'); // 87
        $creator=441;
	$lot_gift_type_id = $t_list->getItemIDFromList('object_lot_types', 'gift'); 
	$accesion_date_id = $t_list->getItemIDFromList('date_types', 'accession');
	$image_type = $t_list->getItemIDFromList('dc_type', 'image');
	$object_document_type = $t_list->getItemIDFromList('object_document_types', 'deed');
	$object_source = $t_list->getItemIDFromList('object_sources', 'permanent_collection');
	$entity_type_id = $t_list->getItemIDFromList('entity_types', 'potential resource');
	echo "Entity type for potential resource is $entity_type_id\n";
	echo "Digitized type is: ";

	echo $dig_img_type_id."\n"."Representation id: ".$vn_rep_type_id."\n";
	$t_rel = new ca_relationship_types();
	$vn_object_occ_ref_rel_id = $t_rel->getRelationshipTypeID('ca_objects_x_occurrences', 'related');
	echo $vn_object_occ_ref_rel_id;  
	$object_num = $object_num + 1;
         echo "object num is : \n";
         echo $object_num;
	$t_object = new ca_objects();
		$t_object->setMode(ACCESS_WRITE);
		$t_object->set('type_id', $dig_img_type_id);
		$t_object->set('idno', $lot_num.'.'.$object_num);
		$t_object->set('access', 1);
		$t_object->set('status', 0);
		$t_object->set('source', 95);
		
                //insertion to include description of object
               
                $t_object->addAttribute(
                            array(
                                 'description'=> $desc,
                                 'locale_id' => $pn_locale_id
                                 ), 'description');

		$t_object->addAttribute(
				array(
					'provenance' => $provenance,
					'locale_id' => $pn_locale_id
				), 'provenance');


		$t_object->addAttribute(
				array(
					'dc_creator' => $creator,
					'locale_id' => $pn_locale_id
				), 'dc_creator');

               //441 is the item_id for "Digitized by donor" from ca_list_items
                  $t_object->addAttribute(
                                 array(
                                       'digitized' => 441,
                                        'locale_id' => $pn_locale_id
                                  ), 'digitized');


  
		$t_object->set('lot_id',$lot_primary_key);
		$t_object->insert();
	       echo "\n object idno generated is:".$lot_num.'.'.$object_num;		
		if ($t_object->numErrors()) {
			print_r($t_object->getErrors());
			//continue;
		}

		if ($t_object->numErrors()) {
			print_r($t_object->getErrors());
			//continue;
		}


		$t_object->addAttribute(
				array(
					'dc_type' => $image_type,
					'locale_id' => $pn_locale_id
				), 'dc_type');

	$t_object->addLabel(array('name' => $filename), $pn_locale_id, null, true);
		  print "\tIMPORTING MEDIA FOR {$vs_path}\n";
			$t_object->addRepresentation($vs_path, $vn_rep_type_id, $pn_locale_id, 4, 1, true);
			if ($t_object->numErrors()) {
				print "ERROR IMPORTING IMAGE @ {$vs_path}: ".join('; ', $t_object->getErrors())."\n";
			}
		

		if ($t_object->numErrors()) {
			print_r($t_object->getErrors());
			//continue;
		}

		//$t_object->addRelationship('ca_entities', $ca_id, $entity_type_id);
		//if ($t_object->numErrors()) {
		//print_R($t_object->getErrors());
		//}     
		//$t_object->addRelationship('ca_entities', $ca_id, 12); // donor type from ca_relationship_types
		//if ($t_object->numErrors()) {
		//print_R($t_object->getErrors());
		//}  
		//$t_object->addRelationship('ca_entities', $ca_id, 18); // potential resource type from ca_relationship_types
		//if ($t_object->numErrors()) {
		//print_R($t_object->getErrorls());
		//} 
   $t_object->addRelationship('ca_entities', $ca_id, 18); // 18 potential resource type// from ca_relationship_types
    //modifications
    $t_object->addRelationship('ca_entities', 81, 12); //12 donor  type from ca_relationship_types, 81 ent_id for 'Voices of Sept11'  

              if ($t_object->numErrors()) {
                print_R($t_object->getErrors());
                }

}
?>
