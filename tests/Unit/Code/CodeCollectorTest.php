<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\GoogleMarketingBundle\Tests\Unit\Code;

use InvalidArgumentException;
use LogicException;
use OpenDxp\Bundle\GoogleMarketingBundle\Code\CodeBlock;
use OpenDxp\Bundle\GoogleMarketingBundle\Code\CodeCollector;
use OpenDxp\Bundle\GoogleMarketingBundle\SiteId\SiteId;
use OpenDxp\Model\Site;

function enrich(CodeCollector $collector, string $block, ?SiteId $siteId = null): string
{
    $codeBlock = new CodeBlock(['code']);
    $collector->enrichCodeBlock($siteId ?? SiteId::forMainDomain(), $codeBlock, $block);

    return $codeBlock->asString();
}

beforeEach(function () {
    $this->collector = new CodeCollector(['head', 'body'], 'head');
});

it('appends a code part to the default block', function () {
    $this->collector->addCodePart('tracking();');

    expect(enrich($this->collector, 'head'))->toBe("code\ntracking();")
        ->and(enrich($this->collector, 'body'))->toBe('code');
});

it('places a code part in the chosen block before or after its code', function () {
    $this->collector->addCodePart('before();', 'body', CodeCollector::ACTION_PREPEND);
    $this->collector->addCodePart('after();', 'body', CodeCollector::ACTION_APPEND);

    expect(enrich($this->collector, 'body'))->toBe("before();\ncode\nafter();");
});

it('adds a code part of a site only to the blocks of that site, and a global one to every site', function () {
    $site = (new Site())->setId(7);

    $this->collector->addCodePart('everywhere();');
    $this->collector->addCodePart('site();', 'head', CodeCollector::ACTION_APPEND, SiteId::forSite($site));

    expect(enrich($this->collector, 'head', SiteId::forSite($site)))->toBe("code\neverywhere();\nsite();")
        ->and(enrich($this->collector, 'head'))->toBe("code\neverywhere();");
});

it('refuses a default block that is not among the valid blocks', function () {
    new CodeCollector(['head', 'body'], 'footer');
})->throws(LogicException::class, 'The default block "footer" must be a part of the valid blocks');

it('refuses an unknown block or action', function (string $block, string $action, string $message) {
    $this->expectExceptionMessage($message);

    $this->collector->addCodePart('tracking();', $block, $action);
})->with([
    'block' => ['footer', CodeCollector::ACTION_APPEND, 'Invalid block "footer". Valid values are: head, body'],
    'action' => ['head', 'merge', 'Invalid action "merge". Valid actions are: prepend, append'],
])->throws(InvalidArgumentException::class);
