<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Ajax;

/**
 * Contains all events thrown in the Ajax Bundle.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
final class AjaxEvents
{
    /**
     * The INJECTION event occurs at pushing ajax data in response.
     *
     * @var string
     */
    const INJECTION = 'sonatra_ajax.injection';
}
