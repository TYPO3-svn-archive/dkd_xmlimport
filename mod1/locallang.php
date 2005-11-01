<?php
/*
 *  CVS Versioning: $Id$
 */

/**
 * Language labels for module "txdkdtoolsM1_txdkdxmimportlM1"
 * 
 * This file is detected by the translation tool.
 */

$LOCAL_LANG = Array (
	'default' => Array (
		'title' => 'XML-Import',	
		'function1' => 'Import XML Data',	
		'function2' => 'Import Files',	
		'function3' => 'Backup Overview',	
		'function4' => 'Function #3',	
		'message1' => 'Add files',
		'message2' => 'View data',
		'message3' => 'Update data',
		'message_list_files' => 'Backup files',
		'message_no_files_in_folder' => 'There are currently no files stored in the upload folder.',
		'message_file_read' => 'Ready to start the import process.',
		'message_no_file_selected' => 'Please select a file from the list above.',
		'file_label' => 'File: ',
		'file_overwrite' => ' Overwrite any existing CSV file!',
		'files_restore_file' => 'Restore db from file.',
		'files_delete_file' => 'Delete file.',
		'files_delete_warning' => 'Are you sure you want to delete this file?',
		'files_restore_warning' => 'Are you sure you want to restore db from this file?',
		'import_preview_settings' => 'Preview settings',
		'import_preview_settings_delimiter' => 'Delimiter',
		'import_preview_settings_preview_limit' => 'Preview limit',
		'import_preview_settings_usefrah' => 'The first row of the file contains the column headers.',
		'import_preview_settings_reload' => 'Reload preview',
		'import_preview' => 'Preview',
		'create_db_table' => 'Create database table',
		'message_table_exists' => 'A table with name "%s" does already exist.',
		'create_table_field' => 'Field',
		'create_table_fieldtype' => 'Type',
		'create_table_fieldlength' => 'Length',
		'create_table_pk' => 'Primary key',
		'create_table' => 'Create database table now?',
		'message_access_denied' => 'You don\'t have sufficient rights to use this module on page "%s" (Page-ID:%u).',
		'message_create_table_error' => 'The table "%s" could not be created. The database says: "%s".',
		'message_insert_error' => 'The data could not be inserted into table "%s". The database says: "%s".',
		'message_insert' => 'The data has been successfully inserted into table "%s", "%s" rows have been created.',
		'update_db_table' => 'Update database table',
		'update_table' => 'Update database table now?',
		'message_table_layout_nomatch' => 'The data cannot be updated. The database table "%s" and the CSV file "%s" do not have the same number of columns.',
		'message_update_delete_error' => 'The data from table "%s" could not be deleted. The database says: "%s".',
		'sql_error' => 'Error:<br>%s generates <em>%s</em>',
		'importXML_header' => 'Import: XML -> DB',
		'importXML_conf_header' => 'Select the settings you want to use for the import',
		'importXML_conf_hint' => '<em>Hint:</em>You will see the configuration in detail after submitting your choice',
		'importXML_backLink' => 'Back',
		'importPics_header' => 'Import: File(DB) -> File',
		'importPics_conf_header' => 'Select the setting you want to use for the file import',
		'importPics_conf_hint' => '<em>Hint:</em>As the import of the files may exceed the max runtime of this script, the files are imported step-by-step',
		'importPics_noPics' => 'There is no file-import defined for the record sets in this table!',
		'importPics_currentSettings' => 'Current settings for the import',
		'importPics_url' => '<p>This is the url where the files are stored for import:<br><em>%s</em></p>',
		'importPics_placeholder_fieldVal' => '&lt;DB-fieldcontent&gt;',
		'importPics_pids_head' => 'Import files related to records on which page?',
		'importPics_pids_all' => 'all',
		'importPics_pic_count' => '%u files have been imported in this step.',
		'importPics_step' => 'Begin / continue import at record ',
		'importPics_completed_msg' => 'Import of %2$u files from %1$u records completed',
		'importPics_backLink' => 'Back',
		'form_select' => 'SELECT',
		'form_import' => 'IMPORT',
		'form_confirm' => 'CONFIRM',
		'form_reset' => 'CANCEL',
	),


	'dk' => Array (
	),


	'de' => Array (
		'title' => 'XML-Import',	
		'function1' => 'Import XML Daten',	
		'function2' => 'Import Dateien',	
		'function3' => 'Backup Übersicht',	
		'function4' => 'Function #3',	
		'message1' => 'Add files',
		'message2' => 'View data',
		'message3' => 'Update data',
		'message_list_files' => 'Backup files',
		'message_no_files_in_folder' => 'There are currently no files stored in the upload folder.',
		'message_file_read' => 'Ready to start the import process.',
		'message_no_file_selected' => 'Please select a file from the list above.',
		'file_label' => 'File: ',
		'file_overwrite' => ' Overwrite any existing CSV file!',
		'files_restore_file' => 'Restore db from file.',
		'files_delete_file' => 'Delete file.',
		'files_delete_warning' => 'Are you sure you want to delete this file?',
		'files_restore_warning' => 'Are you sure you want to restore db from this file?',
		'import_preview_settings' => 'Preview settings',
		'import_preview_settings_delimiter' => 'Delimiter',
		'import_preview_settings_preview_limit' => 'Preview limit',
		'import_preview_settings_usefrah' => 'The first row of the file contains the column headers.',
		'import_preview_settings_reload' => 'Reload preview',
		'import_preview' => 'Preview',
		'create_db_table' => 'Create database table',
		'message_access_denied' => 'Sie haben nicht die nötigen Berechtigungen, um diese Modul auf der Seite "%s" (Page-ID:%u) zu benutzen.',
		'message_table_exists' => 'A table with the name "%s" does already exist.',
		'create_table_field' => 'Feld',
		'create_table_fieldtype' => 'Typ',
		'create_table_fieldlength' => 'Länge',
		'create_table_pk' => 'Primärschlüssel',
		'create_table' => 'Datenbank-Tabelle jetzt anlegen?',
		'message_create_table_error' => 'Die Tabelle "%s" konnte nicht angelegt werden. Die Datenbank meldet folgenden Fehler: "%s".',
		'message_insert_error' => 'Die Daten konnten nicht in die Tabelle "%s" eingefügt werdeen. Die Datenbank meldet folgenden Fehler: "%s".',
		'message_insert' => 'The data has been successfully inserted into table "%s", "%s" rows have been created.',
		'update_db_table' => 'Update database table',
		'update_table' => 'Update database table now?',
		'message_table_layout_nomatch' => 'The data cannot be updated. The database table "%s" and the CSV file "%s" do not have the same number of columns.',
		'message_update_delete_error' => 'The data from table "%s" could not be deleted. The database says: "%s".',
		'sql_error' => 'Fehler:<br>%s erzeugt <em>%s</em>',
		'importXML_header' => 'Import: XML -> DB',
		'importXML_conf_header' => 'Wählen Sie eine Konfiguration aus, die für den Import benutzt werden soll',
		'importXML_conf_hint' => '<em>Hinweis:</em>Eine Beschreibung der Konfiguration sehen sie nach der Bestätigung',
		'importXML_backLink' => 'Zurück',
		'importPics_header' => 'Import: Datei(DB) -> Datei',
		'importPics_conf_header' => 'Wählen Sie die Konfiguration aus, anhand derer die Dateien importiert werden sollen',
		'importPics_conf_hint' => '<em>Hinweis:</em>Da der Import der Dateien einen längeren Zeitraum in Anspruch nehmen kann, als die Laufzeit des Skripts erlaubt, werden die Dateien Schritt-für-Schritt importiert',
		'importPics_noPics' => 'Für die Datensätze in dieser Tabelle sind keine Datei-Importe definiert!',
		'importPics_currentSettings' => 'Aktuelle Einstellungen für den Import',
		'importPics_url' => '<p>Die Dateien für den Import liegen unter der URL:<br><em>%s</em></p>',
		'importPics_placeholder_fieldVal' => '&lt;DB-Feldinhalt&gt;',
		'importPics_pids_head' => 'Dateien zu Datensätzen auf welcher Seite importieren?',
		'importPics_pids_all' => 'alle',
		'importPics_pic_count' => 'In diesem Schritt wurden %u Dateien importiert.',
		'importPics_step' => 'Import beginnen / fortführen bei Datensatz ',
		'importPics_completed_msg' => 'Der Import von %2$u Dateien basierend auf %1$u Datensätzen ist abgeschlossen',
		'importPics_backLink' => 'Zurück',
		'form_select' => 'AUSWÄHLEN',
		'form_import' => 'IMPORTIEREN',
		'form_confirm' => 'BESTÄTIGEN',
		'form_reset' => 'ZURÜCKSETZEN',
	),


	"no" => Array (
	),
	"it" => Array (
	),
	"fr" => Array (
	),
	"es" => Array (
	),
	"nl" => Array (
	),
	"cz" => Array (
	),
	"pl" => Array (
	),
	"si" => Array (
	),
	"fi" => Array (
	),
	"tr" => Array (
	),
	"se" => Array (
	),
	"pt" => Array (
	),
	"ru" => Array (
	),
	"ro" => Array (
	),
	"ch" => Array (
	),
	"sk" => Array (
	),
	"lt" => Array (
	),
	"is" => Array (
	),
	"hr" => Array (
	),
	"hu" => Array (
	),
	"gl" => Array (
	),
	"th" => Array (
	),
	"gr" => Array (
	),
	"hk" => Array (
	),
	"eu" => Array (
	),
	"bg" => Array (
	),
	"br" => Array (
	),
	"et" => Array (
	),
	"ar" => Array (
	),
	"he" => Array (
	),
	"ua" => Array (
	),
	"lv" => Array (
	),
	"jp" => Array (
	),
	"vn" => Array (
	),
);
?>