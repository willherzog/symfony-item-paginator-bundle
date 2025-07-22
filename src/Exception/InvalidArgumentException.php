<?php

namespace WHSymfony\WHItemPaginatorBundle\Exception;

use InvalidArgumentException as OriginalInvalidArgumentException;

class InvalidArgumentException extends OriginalInvalidArgumentException implements ItemPaginatorException
{
}
