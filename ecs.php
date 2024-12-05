<?php

declare(strict_types=1);

use Contao\EasyCodingStandard\Fixer\CommentLengthFixer;
use Contao\EasyCodingStandard\Set\SetList;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withSets([SetList::CONTAO])
    ->withPaths([
        __DIR__.'/src',
    ])
    ->withSkip([
        CommentLengthFixer::class,
    ])
    ->withConfiguredRule(HeaderCommentFixer::class, [
        'header' => "This file is part of the Contao News Filter Event extension.\n\n(c) INSPIRED MINDS\n\n@license LGPL-3.0-or-later",
    ])
    ->withParallel()
    ->withSpacing(lineEnding: "\n")
    ->withCache(sys_get_temp_dir().'/ecs_default_cache')
;
