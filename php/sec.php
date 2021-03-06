<?php
	$SGSERVER = $_GET['SGSERVER'];
	$SGOS = $_GET['SGOS'];
	$SGPANEL = $_GET['SGPANEL'];
	$SGUSER = $_GET['SGUSER'];
	
	define("RCGLOBAL", "Global");
//	define("RCNOCAT", "Unsorted");
	
	function getDesc($file)
	{
		$desc = file($file);
		$desc = preg_grep("/##DESCRP=[[:print:]]+/", $desc);
				if(empty($desc)) {
			return "This does not have a description";
		} else {
			$desc = array_values($desc);
			$desc = trim(preg_replace('/##DESCRP=|\s+|\#|\'/', ' ', $desc[0]));
			return $desc;
		}
	}

	function getFiles($globpat)
	{
		global $SGSERVER, $SGOS, $SGPANEL, $SGUSER;
		$dirs = glob( $globpat , GLOB_BRACE|GLOB_ONLYDIR);
		foreach ($dirs as $old => $dir) {
			$dirs[$dir] = array_diff(scandir($dir), array('..', '.'));
			unset($dirs[$old]);
		}
		
		foreach ($dirs as $dir => $files) {
			$cat = explode('/',$dir);
			$cat = end($cat);
			foreach ($files as $file) {
				$categories[$cat][$dir . "/" . $file] = $file;
			}
			array_unique($categories[$cat]);
			asort($categories[$cat]);
		}
		if (isset($categories)) {
				ksort($categories);
		}
		return $categories;
	}

	$funccategories = getFiles("../functions/{" . $SGSERVER  . "/" . $SGUSER . "/*," . $SGOS  . "/" . $SGUSER . "/*," . $SGPANEL  . "/" . $SGUSER . "/*," . RCGLOBAL . "}");
	$installcategories = getFiles("../installs/{" . $SGSERVER  . "/" . $SGUSER . "/*," . $SGOS  . "/" . $SGUSER . "/*," . $SGPANEL  . "/" . $SGUSER . "/*," . RCGLOBAL . "}");	
	$aliascategories = getFiles("../alias/{" . $SGSERVER  . "/" . $SGUSER . "/*," . $SGOS  . "/" . $SGUSER . "/*," . $SGPANEL  . "/" . $SGUSER . "/*," . RCGLOBAL . ",SSHTOOL}");

		
	switch ($SGPANEL) {
		case "cP":
			if(file_exists("../checks/cP.sh")) {
				require_once("../checks/cP.sh");
			}
			break;
		case "PSA":
			if(file_exists("../checks/PSA.sh")) {
				require_once("../checks/PSA.sh");
			}
			break;
		case "DA":
			if(file_exists("../checks/DA.sh")) {
				require_once("../checks/DA.sh");
			}
			break;
	}
?>

function geninstaller(){
	local action=$1
	<?php
		if (isset($installcategories)) {
			foreach($installcategories as $files) {
				foreach ($files as $path => $file) {
					require_once($path);
					echo "\n";
				}
			}
		}
	?>

	if [ "$action" == "list" ]; then
		<?php
			if (isset($installcategories)) {
				foreach($installcategories as $cat => $files) {
					echo "echo '  " . $cat . " Installs'\n";
					foreach ($files as $path => $file) {
						echo "echo -e \"    " . str_pad($file, 25, " ") . "-- ";
						echo "$(fold -s -w $[$(tput cols) - 32] <<< \"" .getDesc($path) . "\"| sed -e 's/^/\\\\033[33G/g')\"\n";
					}
					echo "echo \n";
				}
			} else {
				echo "echo 'No Available Installs'\n";
			}
		?>
	elif [ "$action" == "install" ]; then
		if [[ $(type -t "${2}-${action}") == "function" ]]; then
			${2}-${action}
		else
			echo "No ${action} action for ${2}"
		fi
	elif [ "$action" == "uninstall" ]; then
		if [[ $(type -t "${2}-${action}") == "function" ]]; then
			${2}-${action}
		else
			echo "No ${action} action for ${2}"
		fi
	elif [ "$action" == "status" ]; then
		if [[ $(type -t "${2}-${action}") == "function" ]]; then
			${2}-${action}
		else
			echo "No ${action} action for ${2}"
		fi
	else
		echo "Usage:"
		echo "    geninstaller [action] [item]"
		echo
		echo "        Actions"
		echo "          install      Install or update the item"
		echo "          uninstall    Uninstall the item"
		echo "          status       Display the installation status of the item"
		echo "          list         Show available items"
	fi
	
	<?php
		if (isset($installcategories)) {
			foreach($installcategories as $files) {
				foreach ($files as $path => $file) {
					echo "unset -f " . $file . "-install\n";
					echo "unset -f " . $file . "-uninstall\n";
					echo "unset -f " . $file . "-status\n";
					echo "\n";
				}
			}
		}
	?>
}

function listfunctions(){
if [ $(tput cols) -le 40 ]; then
	echo "Your terminal is too small."
else
	echo -e "*Functions with a '-h'\n"
	<?php
		if (isset($funccategories)) {
			foreach($funccategories as $cat => $files) {
				echo "echo '  " . $cat . " Functions'\n";
				foreach ($files as $path => $file) {
					echo "echo -e \"    " . str_pad($file, 25, " ") . "-- ";
					echo "$(fold -s -w $[$(tput cols) - 32] <<< \"" .getDesc($path) . "\"| sed -e 's/^/\\\\033[33G/g')\"\n";
				}
				echo "echo \n";
			}
		} else {
			echo "echo 'No Available Functions'\n";
		}
	?>
fi
}


<?php
	if (isset($funccategories)) {
		foreach($funccategories as $files) {
			foreach ($files as $path => $file) {
				require_once($path);
				echo "\n";
			}
		}
	}
?>

function listaliases(){
if [ $(tput cols) -le 40 ]; then
	echo "Your terminal is too small."
else
	<?php
		if (isset($aliascategories)) {
			foreach($aliascategories as $cat => $files) {
				echo "echo '  " . $cat . " Aliases'\n";
				foreach ($files as $path => $file) {
					echo "echo -e \"    " . str_pad($file, 25, " ") . "-- ";
					echo "$(fold -s -w $[$(tput cols) - 32] <<< \"" .getDesc($path) . "\"| sed -e 's/^/\\\\033[33G/g')\"\n";
				}
				echo "echo \n";
			}
		} else {
			echo "echo 'No Available Aliases'\n";
		}
	?>
fi
}

<?php
	if (isset($aliascategories)) {
		foreach($aliascategories as $files) {
			foreach ($files as $path => $file) {
				require_once($path);
				echo "\n";
			}
		}
	}
?>

<?php require("counter.php"); ?>

