<?php

declare(strict_types=1);

use App\Mail\LogMailer;
use Marko\Mail\Message;
use PHPUnit\Framework\TestCase;

class LogMailerTest extends TestCase
{
    private string $tempLogDir;
    private string $tempLogFile;
    private LogMailer $mailer;

    protected function setUp(): void
    {
        $this->tempLogDir = sys_get_temp_dir() . '/marko-test-mail-' . uniqid();
        $this->tempLogFile = $this->tempLogDir . '/mail.log';

        $this->mailer = new LogMailer([
            'path' => $this->tempLogFile,
        ]);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempLogFile)) {
            unlink($this->tempLogFile);
        }

        if (is_dir($this->tempLogDir)) {
            rmdir($this->tempLogDir);
        }
    }

    public function testSendWritesToLogFile(): void
    {
        $message = Message::create()
            ->to('user@example.com', 'Test User')
            ->subject('Test Subject')
            ->html('<p>Hello World</p>');

        $result = $this->mailer->send($message);

        $this->assertTrue($result);
        $this->assertFileExists($this->tempLogFile);

        $contents = file_get_contents($this->tempLogFile);
        $this->assertStringContainsString('user@example.com', $contents);
        $this->assertStringContainsString('Test Subject', $contents);
        $this->assertStringContainsString('Hello World', $contents);
    }

    public function testSendWithMultipleRecipients(): void
    {
        $message = Message::create()
            ->to('alice@example.com', 'Alice')
            ->to('bob@example.com', 'Bob')
            ->subject('Multiple Recipients');

        $this->mailer->send($message);

        $contents = file_get_contents($this->tempLogFile);
        $this->assertStringContainsString('alice@example.com', $contents);
        $this->assertStringContainsString('bob@example.com', $contents);
    }

    public function testSendWithCcAndBcc(): void
    {
        $message = Message::create()
            ->to('to@example.com')
            ->cc('cc@example.com')
            ->bcc('bcc@example.com')
            ->subject('With CC and BCC');

        $this->mailer->send($message);

        $contents = file_get_contents($this->tempLogFile);
        $this->assertStringContainsString('Cc:', $contents);
        $this->assertStringContainsString('Bcc:', $contents);
        $this->assertStringContainsString('cc@example.com', $contents);
    }

    public function testSendWithFrom(): void
    {
        $message = Message::create()
            ->to('user@example.com')
            ->from('noreply@example.com', 'No Reply')
            ->subject('From Address');

        $this->mailer->send($message);

        $contents = file_get_contents($this->tempLogFile);
        $this->assertStringContainsString('From:', $contents);
        $this->assertStringContainsString('noreply@example.com', $contents);
        $this->assertStringContainsString('No Reply', $contents);
    }

    public function testSendRawWritesToLogFile(): void
    {
        $result = $this->mailer->sendRaw('user@example.com', 'Raw email body');

        $this->assertTrue($result);
        $this->assertFileExists($this->tempLogFile);

        $contents = file_get_contents($this->tempLogFile);
        $this->assertStringContainsString('Raw Email', $contents);
        $this->assertStringContainsString('user@example.com', $contents);
        $this->assertStringContainsString('Raw email body', $contents);
    }

    public function testMultipleSendsAppendToLog(): void
    {
        $msg1 = Message::create()->to('a@example.com')->subject('First');
        $msg2 = Message::create()->to('b@example.com')->subject('Second');

        $this->mailer->send($msg1);
        $this->mailer->send($msg2);

        $contents = file_get_contents($this->tempLogFile);
        $this->assertStringContainsString('First', $contents);
        $this->assertStringContainsString('Second', $contents);
    }

    public function testSendWithTextBody(): void
    {
        $message = Message::create()
            ->to('user@example.com')
            ->subject('Text Body')
            ->text('This is a plain text body');

        $this->mailer->send($message);

        $contents = file_get_contents($this->tempLogFile);
        $this->assertStringContainsString('This is a plain text body', $contents);
    }

    public function testCreateLogDirIfNotExists(): void
    {
        $newDir = sys_get_temp_dir() . '/marko-test-mail-new-' . uniqid();
        $newFile = $newDir . '/mail.log';

        $mailer = new LogMailer(['path' => $newFile]);

        $message = Message::create()
            ->to('user@example.com')
            ->subject('New Dir');

        $result = $mailer->send($message);

        $this->assertTrue($result);
        $this->assertFileExists($newFile);
        $this->assertStringContainsString('user@example.com', file_get_contents($newFile));

        // Cleanup
        unlink($newFile);
        rmdir($newDir);
    }
}
