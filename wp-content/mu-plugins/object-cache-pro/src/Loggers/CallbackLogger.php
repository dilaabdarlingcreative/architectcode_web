<?php
/**
 * Copyright © 2019-2025 Rhubarb Tech Inc. All Rights Reserved.
 *
 * The Object Cache Pro Software and its related materials are property and confidential
 * information of Rhubarb Tech Inc. Any reproduction, use, distribution, or exploitation
 * of the Object Cache Pro Software and its related materials, in whole or in part,
 * is strictly forbidden unless prior permission is obtained from Rhubarb Tech Inc.
 *
 * In addition, any reproduction, use, distribution, or exploitation of the Object Cache Pro
 * Software and its related materials, in whole or in part, is subject to the End-User License
 * Agreement accessible in the included `LICENSE` file, or at: https://objectcache.pro/eula
 */

declare(strict_types=1);

namespace RedisCachePro\Loggers;

class CallbackLogger extends Logger
{
    /**
     * The logger's callback function.
     *
     * @var mixed
     */
    protected $callback;

    /**
     * Creates a new callback logger instance.
     *
     * @param  callable  $callback
     * @return void
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param  mixed  $level
     * @param  string  $message
     * @param  array<mixed>  $context
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        if ($this->levels && ! \in_array($level, $this->levels)) {
            return;
        }

        ($this->callback)($level, $message, $context);
    }
}
