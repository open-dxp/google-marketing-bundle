<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\GoogleMarketingBundle\Tests\Unit\Code;

use InvalidArgumentException;
use LogicException;
use OpenDxp\Bundle\GoogleMarketingBundle\Code\CodeBlock;
use OpenDxp\Bundle\GoogleMarketingBundle\Code\CodeCollector;
use OpenDxp\Bundle\GoogleMarketingBundle\SiteId\SiteId;
use OpenDxp\Model\Site;

function codeOf(CodeCollector $collector, SiteId $siteId, string $block): string
{
    $codeBlock = new CodeBlock(['code']);
    $collector->enrichCodeBlock($siteId, $codeBlock, $block);

    return $codeBlock->asString();
}

beforeEach(function () {
    $this->collector = new CodeCollector(['head', 'body'], 'head');
});

it('appends a code part to the default block', function () {
    $this->collector->addCodePart('tracking();');

    $mainDomain = SiteId::forMainDomain();
    expect(codeOf($this->collector, $mainDomain, 'head'))
        ->toBe("code\ntracking();")
        ->and(codeOf($this->collector, $mainDomain, 'body'))
        ->toBe('code');
});

it('places a code part in the chosen block before or after its code', function () {
    $this->collector->addCodePart('before();', 'body', CodeCollector::ACTION_PREPEND);

    $this->collector->addCodePart('after();', 'body', CodeCollector::ACTION_APPEND);

    expect(codeOf($this->collector, SiteId::forMainDomain(), 'body'))->toBe("before();\ncode\nafter();");
});

it('adds a code part of a site only to the blocks of that site, and a global one to every site', function () {
    $site = SiteId::forSite((new Site())->setId(7));
    $this->collector->addCodePart('everywhere();');

    $this->collector->addCodePart('site();', 'head', CodeCollector::ACTION_APPEND, $site);

    expect(codeOf($this->collector, $site, 'head'))
        ->toBe("code\neverywhere();\nsite();")
        ->and(codeOf($this->collector, SiteId::forMainDomain(), 'head'))
        ->toBe("code\neverywhere();");
});

it('refuses a default block that is not among the valid blocks', function () {
    new CodeCollector(['head', 'body'], 'footer');
})->throws(LogicException::class, 'The default block "footer" must be a part of the valid blocks');

it('refuses an unknown block or action', function (string $block, string $action, string $message) {
    expect(fn () => $this->collector->addCodePart('tracking();', $block, $action))
        ->toThrow(InvalidArgumentException::class, $message);
})->with([
    'an unknown block' => [
        'footer',
        CodeCollector::ACTION_APPEND,
        'Invalid block "footer". Valid values are: head, body',
    ],
    'an unknown action' => [
        'head',
        'merge',
        'Invalid action "merge". Valid actions are: prepend, append',
    ],
]);
