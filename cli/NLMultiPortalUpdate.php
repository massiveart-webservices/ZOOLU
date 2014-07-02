<?php

// Zend_Console_Getopt
require_once 'Zend/Console/Getopt.php';

// Create new Colors class
$colors = new Colors();

// define application options and read params from CLI
$getopt = new Zend_Console_Getopt(array(
    'env|e-s'    => 'defines application environment (defaults to "development")',
    'pf|p-s'    => 'defines to field id of portal field',
    'bi|b-s'    => 'which id it should begin to update',
    'help|h'     => 'displays usage information',
));

try {
    $getopt->parse();
}catch (Zend_Console_Getopt_Exception $exc) {
    // Bad options passed: report usage
    echo $exc->getUsageMessage();
    return false;
}

// Show help message in case it was requested or params were incorrect (module, controller and action)
if($getopt->getOption('h')) {
    echo $getopt->getUsageMessage();
    return true;
}

// Define application environment
$env = $getopt->getOption('e');
defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (null === $env) ? 'development' : $env);


// include general (autoloader, config)
require_once(dirname(__FILE__).'/../sys_config/general.inc.php');
/**
 * @var Core $core
 */
$core = Zend_Registry::get('Core');

// Define portal field
$fi = $getopt->getOption('p');
$fieldId = (null === $fi) ? 229 : intval($fi);

$bi = $getopt->getOption('b');
$beginId = (null === $bi) ? 0 : intval($bi);

/**
 * @var Core $core
 */
$dbConfig = $core->dbh->getConfig();

echo PHP_EOL;
printf("Update field with Nr " .  $colors->getColoredString("%s", 'yellow') . " under ".$colors->getColoredString(APPLICATION_ENV . '('.$dbConfig['dbname'].')', 'purple')." begin at subscriber Nr ".$colors->getColoredString(' ' . $beginId . ' ', 'blue', 'light_gray')." Press " .  $colors->getColoredString("CTRL-C", 'red') . " to abort, " .  $colors->getColoredString("enter", 'green') . " to continue", $fieldId);
$fp = fopen("php://stdin","r");
fgets($fp);

echo PHP_EOL . PHP_EOL;

echo 'Try to drop unique key (INDEX) from email subscriber table' . PHP_EOL;

try {
    $core->dbh->query('ALTER TABLE subscribers DROP INDEX email;');
    echo 'OK' . PHP_EOL;
} catch (Exception $e) {
    echo $colors->getColoredString($e->getMessage(), 'red');
    echo PHP_EOL;
    echo PHP_EOL;
}
try {
    echo 'Try to add portal field to subscribers' . PHP_EOL;
    $core->dbh->query('ALTER TABLE  `subscribers` ADD  `portal` BIGINT( 20 ) UNSIGNED NULL AFTER  `idUsers`;');
    echo 'OK' . PHP_EOL;
} catch (Exception $e) {
    echo $colors->getColoredString($e->getMessage(), 'red');
    echo PHP_EOL;
    echo PHP_EOL;
}


try {
    echo 'Try to change portal field' . PHP_EOL;
    $core->dbh->query("UPDATE  `fields` SET  `isCoreField` =  '1' WHERE  `fields`.`id` =".$fieldId.";");
    $core->dbh->query("UPDATE  `fields` SET  `idFieldTypes` =  '20' WHERE  `fields`.`id` =".$fieldId.";");
    echo 'OK' . PHP_EOL;
} catch (Exception $e) {
    echo $colors->getColoredString($e->getMessage(), 'red');
    echo PHP_EOL;
    echo 'Error while updating fields. Program closed!' . PHP_EOL ;
    return;
}



try {
    $query = 'SELECT subscribers.*, im.idRelation as portalmultifield FROM subscribers LEFT JOIN `subscriber-DEFAULT_SUBSCRIBER-1-InstanceMultiFields` as im ON im.idSubscribers = subscribers.id AND im.idFields = '.$fieldId.' AND subscribers.id >= '.$beginId.' ORDER BY subscribers.id';
    $stmt = $core->dbh->query($query);
    $stmt->execute();
} catch (Exception $e) {
    echo $colors->getColoredString($e->getMessage(), 'red');
    echo PHP_EOL;
    echo 'Error while reading subscribers.' . PHP_EOL;
    return;
}

echo PHP_EOL;
echo 'Begin to update subscribers';
echo PHP_EOL;
echo PHP_EOL;

$subscriberList = array();
foreach ($stmt->fetchAll(PDO::FETCH_OBJ) as $row) {
    $portal = $row->portalmultifield;
    if (in_array($row->id, $subscriberList)) {
        // create new subscriber
        echo 'Create Subscriber from ' . $colors->getColoredString($row->id, 'purple') . ' ('.$row->fname.' '.$row->sname.')' . PHP_EOL;
        try {
            createSubscriber($core, (array) $row, $fieldId, $portal);
        } catch (Exception $e) {
            echo $colors->getColoredString($e->getMessage(), 'red');
            echo PHP_EOL;
            echo PHP_EOL;
        }
    } else {
        array_push($subscriberList, $row->id);
        // update subscriber
        if (!empty($portal)) {
            $core->dbh->query('UPDATE subscribers SET portal = '.$portal.' WHERE subscribers.id = ' . $row->id);
            echo 'Set subscriber ' . $colors->getColoredString($row->id, 'green') . ' ('.$row->fname.' '.$row->sname.') to portal ' . $colors->getColoredString($portal, 'yellow') . PHP_EOL;
        } else {
            echo 'Ignore subscriber ' . $colors->getColoredString($row->id, 'green') . ' ('.$row->fname.' '.$row->sname.') no portal found' . PHP_EOL;
        }
    }
    echo PHP_EOL;
}

echo 'Try to delete all multifields from ' . $colors->getColoredString($fieldId, 'yellow') . PHP_EOL;

try {
    $core->dbh->query('DELETE FROM `subscriber-DEFAULT_SUBSCRIBER-1-InstanceMultiFields` WHERE idFields = ' . intval($fieldId));
} catch (Exception $e) {
    echo $colors->getColoredString($e->getMessage(), 'red');
    echo PHP_EOL;
    echo PHP_EOL;
}

echo $colors->getColoredString(' FINISHED!!! ', 'green', 'light_gray');
echo PHP_EOL;
echo PHP_EOL;

/**
 * @param Core $core
 * @param $data
 * @param $portalFieldId
 * @param $portal
 */
function createSubscriber($core, $data, $portalFieldId, $portal)
{
    $colors = new Colors();
    $oldId = $data['id'];
    unset($data['portalmultifield']);
    unset($data['id']);
    $data['portal'] = $portal;
    $core->dbh->insert('subscribers', $data);
    $subScriberId = $core->dbh->lastInsertId();
    echo "Subscriber created with ID " . $colors->getColoredString($subScriberId, 'yellow') . PHP_EOL;

    $stmt = $core->dbh->query('SELECT * FROM `subscriber-DEFAULT_SUBSCRIBER-1-InstanceMultiFields` as im WHERE idFields != '.intval($portalFieldId).' AND idSubscribers = ' . intval($oldId));
    $stmt->execute();


    foreach ($stmt->fetchAll(PDO::FETCH_OBJ) as $row) {
        createMultiField($core, (array)$row, $subScriberId);
    }
}

function createMultiField ($core, $data, $subScriberId)
{
    unset($data['id']);
    $data['idSubscribers'] = $subScriberId;
    $core->dbh->insert('subscriber-DEFAULT_SUBSCRIBER-1-InstanceMultiFields', $data);
}





// Color Library
class Colors {
    private $foreground_colors = array();
    private $background_colors = array();

    public function __construct() {
        // Set up shell colors
        $this->foreground_colors['black'] = '0;30';
        $this->foreground_colors['dark_gray'] = '1;30';
        $this->foreground_colors['blue'] = '0;34';
        $this->foreground_colors['light_blue'] = '1;34';
        $this->foreground_colors['green'] = '0;32';
        $this->foreground_colors['light_green'] = '1;32';
        $this->foreground_colors['cyan'] = '0;36';
        $this->foreground_colors['light_cyan'] = '1;36';
        $this->foreground_colors['red'] = '0;31';
        $this->foreground_colors['light_red'] = '1;31';
        $this->foreground_colors['purple'] = '0;35';
        $this->foreground_colors['light_purple'] = '1;35';
        $this->foreground_colors['brown'] = '0;33';
        $this->foreground_colors['yellow'] = '1;33';
        $this->foreground_colors['light_gray'] = '0;37';
        $this->foreground_colors['white'] = '1;37';

        $this->background_colors['black'] = '40';
        $this->background_colors['red'] = '41';
        $this->background_colors['green'] = '42';
        $this->background_colors['yellow'] = '43';
        $this->background_colors['blue'] = '44';
        $this->background_colors['magenta'] = '45';
        $this->background_colors['cyan'] = '46';
        $this->background_colors['light_gray'] = '47';
    }

    // Returns colored string
    public function getColoredString($string, $foreground_color = null, $background_color = null) {
        $colored_string = "";

        // Check if given foreground color found
        if (isset($this->foreground_colors[$foreground_color])) {
            $colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
        }
        // Check if given background color found
        if (isset($this->background_colors[$background_color])) {
            $colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
        }

        // Add string and end coloring
        $colored_string .=  $string . "\033[0m";

        return $colored_string;
    }

    // Returns all foreground color names
    public function getForegroundColors() {
        return array_keys($this->foreground_colors);
    }

    // Returns all background color names
    public function getBackgroundColors() {
        return array_keys($this->background_colors);
    }
}

