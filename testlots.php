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
	require("addobject.php");
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
        require_once(__CA_LIB_DIR__.'/ca/IDNumbering/MultipartIDNumber.php');

function addlot($lot_num, $filename, $first_name, $middle_name, $last_name, $suffix, $ca_id, $creation_dates, $transfer_date, $number_of_files, $number_of_forms)
{


	$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'en_US');
	
	$t_locale = new ca_locales();
	$g_ui_locale = 'en_US';
	$pn_locale_id = $t_locale->loadLocaleByCode('en_US');		// default locale_id
     

	$t_list= new ca_lists();
	//$vn_rep_type_id	= $t_list->getItemIDFromList('object_representation_types', 'front');
	//$dig_img_type_id = $t_list->getItemIDFromList('object_types', 'image_digital'); // 87
	$lot_gift_type_id = $t_list->getItemIDFromList('object_lot_types', 'gift');
	$yes_no= $t_list->getItemIDFromList('yes_no',"yes"); // 202
	$donation_status = $t_list->getItemIDFromList('donation_status_types','have_digital');
	//$accesion_date_id = $t_list->getItemIDFromList('date_types', 'accession');
	//$image_type = $t_list->getItemIDFromList('dc_type', 'image');
	//$object_document_type = $t_list->getItemIDFromList('object_document_types', 'deed');
	$creation_date_id = $t_list->getItemIDFromList('date_types', 'created');
	$transferred_date_id = $t_list->getItemIDFromList('date_types', 'transferred');//item_id for transferred date is 30771 from the table ca_list_items where list_id for date_types is 35
	$object_lot_document_type = $t_list->getItemIDFromList('object_lot_document_types','curatorial');
           
        //setting up the document folder path
        $vs_doc_path= "/usr2/precaupload/Living Memorial Transferred/Files for Museum_2_7_2011_25profiles/$filename/forms/";



       $desc ="Digital Content scanned or photographed by Voices of September 11th for use in the 9/11 Living Memorial Project, transferred to the 9/11 Memorial Museum for potential use in the memorial exhibition on behalf of $first_name $middle_name $last_name $suffix";
       $provenance = "Voices of September 11th";
       $right_notes = "See Voices Opt-In Forms under \"Documents\"";
       $credit_line = "Courtesy of Voices of September 11th";
       $title = "Voices of September 11th Living Memorial Profile for $first_name $middle_name $last_name $suffix";
       $lot_prefix = 'C.2011';
        
        //updated the media folder to be inside filename folder
	$vs_media_path = "/usr2/precaupload/Living Memorial Transferred/Files for Museum_2_7_2011_25profiles/$filename/Images/";


//lot is created only if folder is accessible
if ($handle = opendir($vs_media_path))
{	
	echo " yes_no id: $yes_no  Lot gift type id: $lot_gift_type_id  Object Lot Document Type Id : $object_lot_document_type\n";

	      $t_lot = new ca_object_lots();
              //to retrieve max value of existing lots
              $t_plug = $t_lot->getIDNoPlugInInstance();
              $lot_num='C.2011.'.$t_plug->getNextValue('lot_number', 'C.2011');
	      $t_lot->setMode(ACCESS_WRITE);
	      $t_lot->set('type_id', $lot_gift_type_id);
              $t_lot->set('idno_stub', $lot_num);
	      $t_lot->set('extent', $number_of_files);
	      //$t_lot->set('credit', 'Courtesy of Voices of September 11th');
	      $t_lot->set('access', 2); // 2 is restricted
	      $t_lot->set('status_type_id', 6);
	      $t_lot->set('lot_status_id', 132); //item_id for pending accession is 132 from table ca_list_items where list_id=14 for object_lots_statuses
	      $t_lot->addAttribute(
				array(
					'donor_opt_in' => $yes_no,
					'locale_id' => $pn_locale_id
				), 'donor_opt_in');

	      $t_lot->insert();	  
	      // Lot id

               $lot_num = $t_lot->get('idno_stub');
	      echo "\n lot no is: ".$lot_num;
	      $t_lot->addLabel(
	      array('name' => $title), $pn_locale_id, null, true
	      );
	      $lot_primary_key = $t_lot->getPrimaryKey();
	      echo "lot primary key: ". $t_lot->getPrimaryKey();
	      if ($t_lot->numErrors()) {
			print_r($t_lot->getErrors());
			//continue;
		}

              //adding description to lots
              $t_lot->addAttribute(
                          array(
                               'description' => $desc,
                               'locale_id' => $pn_locale_id
                              ), 'description');



	      $t_lot->addAttribute(
				array(
					'provenance' => $provenance,
					'locale_id' => $pn_locale_id
				), 'provenance');


	      $t_lot->addAttribute(
				array(
					'rights' => $right_notes,
					'locale_id' => $pn_locale_id
				), 'rights');

	      foreach($creation_dates as $cdate) {
		$t_lot->addAttribute(
				array(
					'dates_value' => $cdate,
					'dc_dates_types' => $creation_date_id,
					'locale_id' => $pn_locale_id
				), 'date');
		}

		$t_lot->addAttribute(
				array(
					'dates_value' => $transfer_date,
					'dc_dates_types' => $transferred_date_id,
					'locale_id' => $pn_locale_id
				), 'date');

		$t_lot->addAttribute(
				array(
					'donation_status' => $donation_status,
					'locale_id' => $pn_locale_id
				), 'donation_status');

		$t_lot->addAttribute(
				array(
					'credit_line' => $credit_line,
					'locale_id' => $pn_locale_id
				), 'credit_line');

	      // check sub elements under System Configuration-metadata elements

            $docFiles = array();
            if($docHandle = opendir($vs_doc_path))
            {
                 while(false !== ($docFile = readdir($docHandle)))
                  {
                      if($docFile != "." && $docFile != "..")
                      {
                      $docFiles[] = $docFile;
                      echo $docFile;
                      }
                  }
                  closedir($docHandle);
             }

              print_r($docFiles);
                
             //to test with an array of docs
            foreach($docFiles as $docname) 
            {
                   $vs_finaldoc_path= $vs_doc_path.$docname;
                   $t_lot->addAttribute(
                        array(
                            'objectLotDocType' => $object_lot_document_type,
                            'objectLotDocFile' => $vs_finaldoc_path,
                        'objectLotDocDescription' => 'lot document',
                            'locale_id' => $pn_locale_id
                            ), 'object_lot_documents');

               } 

            
	      $t_lot->update();

	      if ($t_lot->numErrors()) {
			print_r($t_lot->getErrors());
			//continue;
		}

        	$ext = '';
      // File Handling
	$files = array();
	if ($handle = opendir($vs_media_path)) {
	  while (false !== ($file = readdir($handle))) {
	      if ($file != "." && $file != "..") {
		  echo "$file\n";
		  $t = explode('.',$file);
		  $ext = strtolower(array_pop($t));
		  echo $ext;
		  $files[] = $file;
	      }
	  }
	  closedir($handle);
	}
	print_r($files);
    
       $full_name = $first_name.' '.$middle_name.' '.$last_name.' '.$suffix;
       echo "\n full name is :";
       echo $full_name;


foreach($files as $imagename)
{
$vs_path = $vs_media_path.$imagename;

print "$lot_primary_key : $lot_num : $imagename : $ca_id : $vs_path \n";
addobject($lot_primary_key, $lot_num,$imagename,$ca_id,$vs_path, $object_num, $full_name);
$object_num = $object_num + 1;
}


}




}
?>
