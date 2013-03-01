<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

$cmd = $console->register('blog:email');

$cmd->setDescription('Sends email queue');

$cmd->setCode(function(InputInterface $in, OutputInterface $out) {
    $emails = service('db')->all("SELECT * FROM messages");
    if (count($emails)) {
        service('db')->query('BEGIN');
        $out->writeLn('Sending: <info>' . count($emails) . '</info> emails..');
        foreach ($emails as $email) {
            $message = Swift_Message::newInstance()
                ->setSubject($email['subject'])
                ->setCharset('utf-8')
                ->setFrom(array($email['email'] => $email['sender']))
                ->setReplyTo(array($email['email'] => $email['sender']))
                ->setTo('gediminas.morkevicius@gmail.com');

            $message->addPart($email['content'], 'text/html', 'utf-8');
            try {
                if (!service('swiftmailer')->send($message)) {
                    service('logger')->push($msg = "Failed to send message [{$email['subject']}]")->flush();
                    $out->writeLn($msg);
                } else {
                    service('db')->delete('messages', array('id' => $email['id']));
                }
            } catch (\Exception $e) {
                service('logger')->push($msg = "Failed to send message [{$email['subject']}] reason [{$e->getMessage()}]")->flush();
                $out->writeLn($msg);
            }
        }
        service('db')->query('COMMIT');
    } else {
        $out->writeLn('No new emails..');
    }
});
