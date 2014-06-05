<?php
/**
 * All exceptions lives in root namespace
 */

class AlreadyConnectedException extends \Exception
{
}

class ConnectionFailedException extends \Exception
{
}

class InvalidQueryException extends \Exception
{
}

class InvalidResultException extends \Exception
{
}

class DirectoryNotFoundRexception extends \Exception
{
}

class FileNotFoundException extends \Exception
{
}

class WritePermissionDeniedException extends \Exception
{
}

/**
 * The result of this call is cached in the client,
 * so we don't need to send the document
 */
class CachedInClientException extends \Exception
{
}
