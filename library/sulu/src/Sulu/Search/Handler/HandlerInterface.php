<?php

/*
 * This file is part of the Search package.
 *
 * (c) Cornelius Hansjakob <cha@massiveart.com>
 *
 */

namespace Sulu\Search\Handler;

/**
 * Interface that all Search Handlers must implement
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 */
interface HandlerInterface
{

    public function add($key, $data);

    public function delete($key);

}
