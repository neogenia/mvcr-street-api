<?php

namespace StreetApi;


class InvalidArgumentException extends \InvalidArgumentException
{
}


class RuntimeException extends \RuntimeException
{
}


class InvalidStateException extends RuntimeException
{
}


class LogicException extends \LogicException
{
}


class MemberAccessException extends LogicException
{
}


class NotImplementedException extends LogicException
{
}


class NotSupportedException extends LogicException
{
}
