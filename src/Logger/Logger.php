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
class Logger {
    /** @var \Monolog\Logger */
    protected $logger;
    protected $levels;

    /**
     * Crea un log, in tre formati:
     * - file di testo
     * - tabella su un database
     * - email.
     * Almeno uno dei tre formati deve essere fornito, altrimenti viene creato un log testuale nella cartella corrente con nome $level-$channel-log.log.
     */
    public function __construct($channel, $level, $file_log=null, $db=null, $emailer=null)
    {
        $this->logger = new \Monolog\Logger($channel);
        $this->levels = $this->logger->getLevels();
        if(!in_array($level, array_keys($this->levels))) {
            $validLevels = implode(', ', $this->levels);
            throw new InvalidArgumentException("Livello non riconosciuto. Livello fornito: $level. Valori accettati: [$validLevels].");
        }
        if(empty($file_log) && empty($db) && empty($emailer)) {
            $file_log = __DIR__."/$level-$channel-log.log";
        }
        $numericLevel = $this->levels[$level];
        if(!empty($file_log)) {
            $this->logger->pushHandler(new StreamHandler($file_log, $numericLevel));
        }
        if($db instanceof PDO) {
            $this->logger->pushHandler(new PDOHandler($db, $numericLevel));
        }
        if($emailer instanceof PHPMailer) {
            $this->logger->pushHandler(new PHPMailerHandler($emailer, $level));
        }
    }

    public function getLogger() {
        return $this->logger;
    }

    /**
     * Restituisce un oggetto monolog\Logger, invece di avere l'oggetto Up3Up\Logger.
     */
    public static function get_logger($channel, $level, $file_log=null, $db=null, $emailer=null) {
        $logger = new Logger($channel, $level, $file_log, $db, $emailer);
        return $logger->getLogger();
    }
}