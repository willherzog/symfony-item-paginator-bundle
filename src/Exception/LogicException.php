<?php

namespace WHSymfony\WHItemPaginatorBundle\Exception;

use LogicException as OriginalLogicException;

class LogicException extends OriginalLogicException implements ItemPaginatorException
{
}
