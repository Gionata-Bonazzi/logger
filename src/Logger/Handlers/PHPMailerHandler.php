<?php
namespace Up3Up\Logger\Handlers;
 
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Invia una email usando PHPMailer.
 * Invia informazioni base, ma se site_url viene messo nel contesto viene inserito sia nell'oggetto, sia nel corpo dell'email.
 * Questo è utile se l'indirizzo email del mittente, o del destinatario, sono usati per più progetti che hanno dei log.
 * Comunque di base il nome del canale dovrebbe bastare per identificare il log.
 */
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