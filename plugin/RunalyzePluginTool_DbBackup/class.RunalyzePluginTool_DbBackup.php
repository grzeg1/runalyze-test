<?php
/**
 * This file contains the class of the RunalyzePluginTool "DbBackup".
 * @package Runalyze\Plugins\Tools
 */
$PLUGINKEY = 'RunalyzePluginTool_DbBackup';
/**
 * Class: RunalyzePluginTool_DbBackup
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Tools
 */
class RunalyzePluginTool_DbBackup extends PluginTool {
	/**
	 * Start of backup-files
	 * @var string 
	 */
	protected $fileNameStart = '';

	/**
	 * Path for all backups, relative to FRONTEND_PATH
	 * @var string
	 */
	protected $BackupPath = '../plugin/RunalyzePluginTool_DbBackup/backup/';

	/**
	 * Export type: *.json
	 * @var enum
	 */
	static private $TYPE_JSON = 1;

	/**
	 * Export type: *.sql.gz
	 * @var enum
	 */
	static private $TYPE_SQL = 2;

	/**
	 * ImportData: json 
	 * @var array
	 */
	protected $ImportData = array();

	/**
	 * ImportData Replaces Array 
	 * @var array
	 */
	protected $ImportReplace = array();

	/**
	 * Boolean flag: import on progress
	 * @var boolean
	 */
	protected $importIsOnProgress = false;

	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$TOOL;
		$this->name = __('Database import/export');
		$this->description = __('This plugin allows you to import and export your complete data from the database.');

		$this->fileNameStart = SessionAccountHandler::getId().'-runalyze-backup';

		if (isset($_GET['json'])) {
			if (move_uploaded_file($_FILES['qqfile']['tmp_name'], realpath(dirname(__FILE__)).'/import/'.$_FILES['qqfile']['name'])) {
				Error::getInstance()->footer_sent = true;
				echo '{"success":true}';
			} else {
				echo '{"error":"Moving file did not work. Set chmod 777 for '.realpath(dirname(__FILE__)).'/import/"}';
			}

			exit;
		}
	}

	/**
	 * Set default config-variables
	 * @see PluginPanel::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();

		return $config;
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		$this->handleRequest();
		$this->displayExport();
		$this->displayImport();
		$this->displayList();
	}

	/**
	 * Handle request 
	 */
	protected function handleRequest() {
		if (isset($_GET['file']) || isset($_POST['file'])) {
			require_once FRONTEND_PATH.'../plugin/'.$this->key.'/class.RunalyzeJsonImporter.php';

			$this->importIsOnProgress = true;
			// Rest will be done in $this->displayImport();
		}

		if (isset($_POST['backup'])) {
			if ($_POST['export-type'] == 'json')
				$this->createBackupJSON();
			else
				$this->createBackupSQL();
		}
	}

	/**
	 * Display export form 
	 */
	protected function displayExport() {
		$Select = new FormularSelectBox('export-type', __('File format'));
		$Select->addOption('json', __('Portable backup').' (*.json.gz)');
		$Select->addOption('sql', __('Database backup').' (*.sql.gz)');

		$Fieldset = new FormularFieldset( __('Export your data') );
		$Fieldset->addField($Select);
		$Fieldset->addField(new FormularSubmit(__('Create file'), ''));
		$Fieldset->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W50 );
		$Fieldset->addInfo('<strong>'.__('JSON-format').' (*.json.gz)</strong><br>
			<small>'.
				__('Portable backup of your configuration and data -'.
					'This file can be imported into any other installation, using this plugin.<br />'.
					'This way you can transfer your data from to local to an online installation and back.').'
			</small>');
		$Fieldset->addInfo('<strong>'.__('SQL-format').' (*.sql.gz)</strong><br>
			<small>'.
				__('Backup of the complete database -'.
					'This file can be imported manually with e.g. PHPMyAdmin into any database.<br />'.
					'This is recommended to create a backup copy or to import your data into a new installation.').'
			</small>');

		if ($this->importIsOnProgress)
			$Fieldset->setCollapsed();

		$Formular = new Formular( $_SERVER['SCRIPT_NAME'].'?id='.$this->id );
		$Formular->setId('database-backup');
		$Formular->addCSSclass('ajax');
		$Formular->addCSSclass('no-automatic-reload');
		$Formular->addFieldset($Fieldset);
		$Formular->addHiddenValue('backup', 'true');
		$Formular->display();
	}

	/**
	 * Display import form
	 */
	protected function displayImport() {
		if (isset($_GET['file'])) {
			$this->displayImportForm();
		} elseif (isset($_POST['file'])) {
			$this->displayImportFinish();
		} else {
			$this->displayImportUploader();
		}
	}

	/**
	 * Display import form 
	 */
	protected function displayImportForm() {
		$Fieldset = new FormularFieldset( __('Import file') );

		$Formular = new Formular( $_SERVER['SCRIPT_NAME'].'?id='.$this->id );
		$Formular->setId('import-json-form');
		$Formular->addCSSclass('ajax');
		$Formular->addCSSclass('no-automatic-reload');
		$Formular->addHiddenValue('file', $_GET['file']);

		if (substr($_GET['file'], -8) != '.json.gz') {
			$Fieldset->addError( __('You can only import *.json.gz-files.'));

			Filesystem::deleteFile('../plugin/'.$this->key.'/import/'.$_GET['file']);
		} else {
			$Importer = new RunalyzeJsonImporter('../plugin/'.$this->key.'/import/'.$_GET['file']);
			$Errors   = $Importer->getErrors();

			if (empty($Errors)) {
				$Fieldset->addField( new FormularCheckbox('overwrite_config', __('Overwrite general configuration'), true) );
				$Fieldset->addField( new FormularCheckbox('overwrite_dataset', __('Overwrite dataset configuration'), true) );
				$Fieldset->addField( new FormularCheckbox('overwrite_plugin_conf', __('Overwrite plugin configuration'), true) );
				$Fieldset->addField( new FormularCheckbox('delete_trainings', __('Delete all old activities'), false) );
				$Fieldset->addField( new FormularCheckbox('delete_user_data', __('Delete all old body values'), false) );
				$Fieldset->addField( new FormularCheckbox('delete_shoes', __('Delete all old shoes'), false) );

				$Fieldset->addFileBlock( sprintf( __('There are <strong>%s</strong> activities in this file.'), $Importer->getNumberOfTrainings()) );
				$Fieldset->addFileBlock( sprintf( __('There are <strong>%s</strong> shoes in this file.'), $Importer->getNumberOfShoes()) );
				$Fieldset->addFileBlock( sprintf( __('There are <strong>%s</strong> body values in this file.'), $Importer->getNumberOfUserData()) );

				$Fieldset->setLayoutForFields(FormularFieldset::$LAYOUT_FIELD_W100);

				$Formular->addSubmitButton( __('Import') );
			} else {
				$Fieldset->addError( __('The file seems to be corrupted.') );

				foreach ($Errors as $Error)
					$Fieldset->addError($Error);
			}
		}

		$Formular->addFieldset($Fieldset);
		$Formular->display();
	}

	/**
	 * Display form: import finished 
	 */
	protected function displayImportFinish() {
		$Importer = new RunalyzeJsonImporter('../plugin/'.$this->key.'/import/'.$_POST['file']);
		$Importer->importData();

		$Errors   = $Importer->getErrors();
		$Fieldset = new FormularFieldset( __('Import data') );

		if (empty($Errors)) {
			$Fieldset->addInfo( __('All data have been imported.') );

			Ajax::setReloadFlag(Ajax::$RELOAD_ALL);
			$Fieldset->addBlock(Ajax::getReloadCommand());
		} else {
			$Fieldset->addError( __('There was a problem with the import.') );

			foreach ($Errors as $Error)
				$Fieldset->addError($Error);
		}

		$Formular = new Formular();
		$Formular->setId('import-finished');
		$Formular->addFieldset($Fieldset);
		$Formular->display();
	}

	/**
	 * Display uploader 
	 */
	protected function displayImportUploader() {
		$JScode = '
			new qq.FineUploaderBasic({
				button: $("#file-upload")[0],
				request: {
					endpoint: \''.$_SERVER['SCRIPT_NAME'].'?hideHtmlHeader=true&id='.$this->id.'&json=true\'
				},
				callbacks: {
					onError: function(id, name, errorReason, xhr) {
						$("#ajax").append(\'<p class="error appended-by-uploader">\'+errorReason+\'</p>\');
					},
					onComplete: function(id, fileName, responseJSON) {
						$(".appended-by-uploader").remove();
						$("#ajax").loadDiv(\''.$_SERVER['SCRIPT_NAME'].'?id='.$this->id.'&file=\'+encodeURIComponent(fileName));

						if (!responseJSON.success) {
							if (responseJSON.error == "")
								responseJSON.error = \'An unknown error occured.\';
							$("#ajax").append(\'<p class="error appended-by-uploader">\'+fileName+\': \'+responseJSON.error+\'</p>\');
							$("#upload-container").removeClass("loading");
						}
					}
				}
			});';

		$Text = '<div id="upload-container" style="margin-bottom:5px;"><div class="c button" id="file-upload">'.__('Upload file').'</div></div>';
		$Text .= Ajax::wrapJSasFunction($JScode);
		$Text .= HTML::info( __('Allowed file extension: *.json.gz') );
		$Text .= HTML::warning( __('The file has to be created with the same version of Runalyze!<br>'.
									'You won\'t be able to import a file from an older version.') );

		$Fieldset = new FormularFieldset( __('Import data') );
		$Fieldset->setCollapsed();
		$Fieldset->addBlock($Text);

		$Formular = new Formular();
		$Formular->setId('backup-import');
		$Formular->addFieldset($Fieldset);
		$Formular->display();
	}

	/**
	 * Display list with files 
	 */
	protected function displayList() {
		$ListOfFiles = $this->getExistingFiles();

		$Fieldset = new FormularFieldset( __('Export data') );

		if (empty($ListOfFiles)) {
			$Fieldset->addFileBlock('<em>You did not export anything.</em>');
		} else {
			foreach ($ListOfFiles as $File) {
				$String = '';

				$FileNameParts = explode('-', $File);
				$Year          = isset($FileNameParts[3]) ? $FileNameParts[3] : '';
				if (strlen($Year) == 8)
					$String .= '<strong>'.substr($Year, 6, 2).'.'.substr($Year, 4, 2).'.'.substr($Year, 0, 4).':</strong> ';

				$String .= $File;
				$String .= ', '.Filesystem::getFilesize(FRONTEND_PATH.$this->BackupPath.$File);

				$Fieldset->addFileBlock('<a href="inc/'.$this->BackupPath.$File.'" target="_blank">'.$String.'</a>');
			}
		}

		if ($this->importIsOnProgress)
			$Fieldset->setCollapsed();

		$Formular = new Formular();
		$Formular->setId('backup-list');
		$Formular->addFieldset($Fieldset);
		$Formular->display();
	}

	/**
	 * Get array with all existing 
	 * @return array 
	 */
	protected function getExistingFiles() {
		$Files = array();
		if ($handle = opendir(FRONTEND_PATH.$this->BackupPath)) {
			while (false !== ($file = readdir($handle))) {
				if (substr($file,0,1) != ".") {
					if (strpos($file, $this->fileNameStart) === 0)
						$Files[] = $file;
				}
			}

			closedir($handle);
		}

		return $Files;
	}
	/**
	 * Create backup: JSON
	 */
	protected function createBackupJSON() {
		$Writer = new BigFileWriterGZip($this->getFileName(self::$TYPE_JSON));
		$DB     = DB::getInstance();

		$DB->stopAddingAccountID();
		$AllTables = $DB->query('SHOW TABLES')->fetchAll();
		$DB->startAddingAccountID();

		$Writer->addToFile('{');

		foreach ($AllTables as $t => $Tables) {
			if ($t > 0)
				$Writer->addToFile(',');

			foreach ($Tables as $TableName) {

				$Query = 'SELECT * FROM `'.$TableName.'`';
				$Writer->addToFile('"'.$TableName.'":{');

				if ($TableName == PREFIX.'account') {
					if (USER_MUST_LOGIN)
						$Query .= ' WHERE id="'.SessionAccountHandler::getId().'"';
					else
						$Query .= ' WHERE id="0"';
				}
				

				$ArrayOfRows = $DB->query($Query)->fetchAll();

				foreach ($ArrayOfRows as $r => $Row) {
					if ($r > 0)
						$Writer->addToFile(',');

					if (PREFIX != 'runalyze_')
						$TableName = str_replace(PREFIX, 'runalyze_', $TableName);

					// Don't save the cache!
					if ($TableName == 'runalyze_training')
						$Row['gps_cache_object'] = '';

					$Writer->addToFile('"'.$Row['id'].'":'.json_encode($Row));
				}

				$Writer->addToFile('}');
			}
		}

		$Writer->addToFile('}');
		$Writer->finish();
	}

	/**
	 * Create backup: SQL
	 */
	protected function createBackupSQL() {
		$Writer = new BigFileWriterGZip($this->getFileName(self::$TYPE_SQL));
		$DB     = DB::getInstance();

		$DB->stopAddingAccountID();
		$AllTables = $DB->query('SHOW TABLES')->fetchAll();
		$DB->startAddingAccountID();

		foreach ($AllTables as $Tables) {
			foreach ($Tables as $TableName) {
				$DB->stopAddingAccountID();
				$CreateResult = $DB->query('SHOW CREATE TABLE '.$TableName)->fetchAll();
				$ColumnInfo   = $DB->query('SHOW COLUMNS FROM '.$TableName)->fetchAll();
				$DB->startAddingAccountID();

				$Query        = 'SELECT * FROM `'.$TableName.'`';

				if ($TableName == PREFIX.'account') {
					if (USER_MUST_LOGIN)
						$Query .= ' WHERE id="'.SessionAccountHandler::getId().'"';
					else
						$Query .= ' WHERE id="0"';
				}

				$ArrayOfRows = $DB->query($Query)->fetchAll(PDO::FETCH_NUM);
				$Writer->addToFile('DROP TABLE IF EXISTS '.$TableName.';'.NL.NL);
				$Writer->addToFile($CreateResult[0]['Create Table'].';'.NL.NL);

				foreach ($ArrayOfRows as $Row) {
					foreach ($Row as $i => $Value) {
						if (empty($Value) && !is_numeric($Value) && $ColumnInfo[$i]['Null'] == 'YES')
							$Row[$i] = null;
					}

					$Values = implode(',', array_map('DB_BACKUP_mapperForValues', $Row));
					$Writer->addToFile('INSERT INTO '.$TableName.' VALUES('.$Values.');'.NL);
				}

				$Writer->addToFile(NL.NL.NL);
			}
		}

		$Writer->finish();

		//Error::getInstance()->addDebug('Memory usage: '.memory_get_peak_usage().' (SQL)');
	}

	/**
	 * Get filename
	 * @param enum $Type
	 * @return string
	 */
	protected function getFileName($Type) {
		if ($Type == self::$TYPE_SQL) {
			$FileType = '.sql.gz';
		} else if ($Type == self::$TYPE_JSON) {
			$FileType = '.json.gz';
		}

		return $this->BackupPath.$this->fileNameStart.'-'.date('Ymd-Hi').'-'.substr(uniqid(rand()),-4).$FileType;
	}
}

/**
 * Mapper for values of a row
 * @param string $v
 * @return string
 */
function DB_BACKUP_mapperForValues($v) {
	if (is_null($v))
		return 'NULL';

	return '"'.str_replace("\n", "\\n", addslashes($v)).'"';
}