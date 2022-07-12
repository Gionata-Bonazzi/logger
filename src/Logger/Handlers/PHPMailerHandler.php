<?php
namespace Up3Up\Logger\Handlers;
 
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use PHPMailer\PHPMailer\PHPMailer;

class PHPMailerHandler extends AbstractProcessingHandler
{
    private $mailer;

    public function __construct(PHPMailer $mailer, $level = Logger::CRITICAL, bool $bubble = true)
    {
        $this->mailer = $mailer;
        parent::__construct($level, $bubble);
    }

    protected function write(array $record): void
    {
        $sito = $record['context']['site_url'] ?? null;
        $subject = "[{$record['level_name']}] : Errore inatteso che richiede attenzione immediata" . ( (isset($sito)) ? " sul sito $sito." : ".") . ' ' . $record['datetime']->format('Y-m-d H:i:s');
        $body = 
        "Canale: {$record['channel']}".'<br>'.
        "Codice: {$record['level']}".'<br>'.
        "Livello: {$record['level_name']}".'<br>'.
        ( (isset($sito)) ? "Sito: {$sito}<br>" : "" ).
        "Messaggio breve: {$record['message']}".'<br>'.
        "Messaggio: {$record['formatted']}".'<br>'.
        "Orario: " . $record['datetime']->format('Y-m-d H:i:s'). '<br>';
        $this->mailer->Subject = $subject;
        $this->mailer->msgHTML($body);
        $this->mailer->send();
    }
}