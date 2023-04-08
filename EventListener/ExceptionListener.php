<?php

namespace Enemis\SonataMediaLiipImagineBundle\EventListener;

use Liip\ImagineBundle\Exception\ExceptionInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Imagine\Exception\InvalidArgumentException;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        if ($event->getRequestType() === HttpKernelInterface::MASTER_REQUEST) {
            return;
        }
        if ($exception instanceof \Imagine\Exception\InvalidArgumentException
            || $exception instanceof ExceptionInterface) {
            throw $exception;
        }
    }
}
