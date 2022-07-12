<?php
namespace Up3Up\Logger\Handlers;
 
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

class PDOHandler extends AbstractProcessingHandler
{
    private $initialized = false;
    private $pdo;
    private $statement;

    public function __construct(\PDO $pdo, $level = Logger::DEBUG, bool $bubble = true)
    {
        $this->pdo = $pdo;
        parent::__construct($level, $bubble);
    }

    protected function write(array $record): void
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        $this->statement->execute(array(
            'channel' => $record['channel'],
            'level' => $record['level'],
            'level_name' => $record['level_name'],
            'message_short' => $record['message'],
            'message' => $record['formatted'],
            'time' =>$record['datetime']->format('U'),
        ));
    }

    private function initialize()
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS log '
            .'(channel VARCHAR(255), level INTEGER, level_name VARCHAR(255), message_short LONGTEXT, message LONGTEXT, time DATETIME)'
        );
        $this->statement = $this->pdo->prepare(
            'INSERT INTO log (channel, level, level_name, message_short, message, time) VALUES (:channel, :level, :level_name, :message_short, :message, FROM_UNIXTIME(:time))'
        );

        $this->initialized = true;
    }

}