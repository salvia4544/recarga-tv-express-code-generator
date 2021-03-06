<?php

namespace CViniciusSDias\RecargaTvExpress\Tests\Unit\Service;

use CViniciusSDias\RecargaTvExpress\Service\EmailParser\EmailParser;
use CViniciusSDias\RecargaTvExpress\Service\EmailSalesReader;
use PhpImap\Mailbox;
use PHPUnit\Framework\TestCase;

class SalesFinderTest extends TestCase
{
    public function testShouldReturnEmptyArrayIfThereAreNoEmails()
    {
        $mailbox = $this->createStub(Mailbox::class);

        $mailbox->method('searchMailbox')
            ->willReturn([]);

        /** @var EmailParser $nullParser */
        $nullParser = $this->createStub(EmailParser::class);
        $salesFinder = new EmailSalesReader($mailbox, $nullParser);
        $sales = $salesFinder->findSales();

        $this->assertCount(0, $sales);
    }
}
