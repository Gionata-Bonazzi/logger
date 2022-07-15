<?php
namespace Up3Up\Logger;

use InvalidArgumentException;
use Monolog\Handler\StreamHandler;
use PDO;
use PHPMailer\PHPMailer\PHPMailer;
use Up3Up\Logger\Handlers\PDOHandler;
use Up3Up\Logger\Handlers\PHPMailerHandler;

/**
 * Livelli di log:
 * DEBUG (100): Detailed debug information.
 * INFO (200): Interesting events. Examples: User logs in, SQL logs.
 * NOTICE (250): Normal but significant events.
 * WARNING (300): Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
 * ERROR (400): Runtime errors that do not require immediate action but should typically be logged and monitored.
 * CRITICAL (500): Critical conditions. Example: Application component unavailable, unexpected exception.
 * ALERT (550): Action must be taken immediately. Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.
 * EMERGENCY (600): Emergency: system is unusable.
 */
class Logger extends \Monolog\Logger{

    public function __construct($channel)
    {
        parent::__construct($channel);
    }

    public function pushEmailHandler($emailer, $level) {
        $result = false;
        if($emailer instanceof PHPMailer) {
            $this->pushHandler(new PHPMailerHandler($emailer, $level));
            $result = true;
        }
        return $result;
    }

    public function pushPDOHandler($db, $level) {
        $result = false;
        if($db instanceof PDO) {
            $this->pushHandler(new PDOHandler($db, $level));
            $result = true;
        }
        return $result;
    }

    public function pushTextHandler($file, $level) {
        $result = false;
        if(!empty($file)) {
            $this->pushHandler(new StreamHandler($file, $level));
            $result = true;
        }
        return $result;
    }
    
    public function checkLevel($level) {
        return in_array($level, array_keys($this->levels));
    }

    /**
     * Restituisce un oggetto monolog\Logger, invece di avere l'oggetto Up3Up\Logger, già preparato con gli handler collegati al livello passato.
     * Questo permette di creare un logger con solo un livello minimo di log per tutti gli handler.
     */
    public static function get_logger($channel, $level, $file_log=null, $db=null, $emailer=null) {
        $logger = new Logger($channel);
        $logger->pushEmailHandler($emailer, $level);
        $logger->pushPDOHandler($db, $level);
        $logger->pushTextHandler($file_log, $level);
        return $logger;
    }
}