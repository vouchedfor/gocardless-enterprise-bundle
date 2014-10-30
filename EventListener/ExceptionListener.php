<?php

namespace Vouchedfor\GocardlessEnterpriseBundle\EventListener;

use GoCardless\Enterprise\Exceptions\ApiException;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    protected $kernel;

    public function __construct(\Symfony\Component\HttpKernel\Kernel $kernel )
    {
        $this->kernel = $kernel;
    }

    public function onConsoleException(ConsoleExceptionEvent $event)
    {
        $exception = $event->getException();

        //This listener only wants to deal with this particular Exception
        if ( !($exception instanceof \GoCardless\Enterprise\Exceptions\ApiException) )
        {
            return;
        }

        $output = $event->getOutput();

        $rawReasonPhrase = $exception->getReasonPhrase();

        $data = json_decode($rawReasonPhrase, true );

        $exceptionName = get_class($exception);
        $gocardlessErrorMessage = isset($data['error']['message']) ? $data['error']['message'] : 'No Gocardless error available';
        $fieldErrors = isset($data['error']['errors']) ? $data['error']['errors'] : array();

        $output->writeln("");
        $output->writeln("Exception: {$exceptionName}");
        $output->writeln("---------");
        $output->writeln("Gocardless error message: {$gocardlessErrorMessage}");

        if ($fieldErrors)
        {
            $output->writeln("");
            $output->writeln("Field Specific error messages");
        }

        foreach ($fieldErrors as $error)
        {
            $output->writeln("");
            foreach ($error as $k => $e)
            {
                $output->writeln("{$k}: {$e}");
            }
            $output->writeln("-----------");
        }

        $output->writeln("LOOK ABOVE FOR MORE INFORMATION ABOUT THIS EXCEPTION");

    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        //Only change the response if in debug mode.
        if (! $this->kernel->isDebug() )
        {
            return;
        }


        $exception = $event->getException();

        //This listener only wants to deal with this particular Exception
        if ( !($exception instanceof \GoCardless\Enterprise\Exceptions\ApiException) )
        {
            return;
        }


        /******* TODO: Start code which is specific to VouchedFor only (Refactor/remove this if this bundle is made public)
         * This is just a real quick hack to make the other VOuchedFor ExceptionListener handle this instead*****/
        $request = $event->getRequest();
        if ( strpos($request->getPathInfo(),'/api/') === 0 )
        {
            return;
        }
        /******* End code which is specific to VouchedFor only *****/



        $statusCode = $exception->getResponse()->getStatusCode();

        $rawReasonPhrase = $exception->getReasonPhrase();

        $data = json_decode($rawReasonPhrase, true );

        $exceptionName = get_class($exception);
        $gocardlessErrorMessage = isset($data['error']['message']) ? $data['error']['message'] : 'No Gocardless error available';
        $fieldErrors = isset($data['error']['errors']) ? $data['error']['errors'] : array();

        $message = "
            <h1>Exception: {$exceptionName}</h1>
            <p><b>Gocardless error message:</b> {$gocardlessErrorMessage}</p>
            <p><b>Status Code:</b> {$statusCode}</p>
            ";

        if ($fieldErrors)
        {
            $message .= "<h3>Field Specific error messages</h3>";
        }

        foreach ($fieldErrors as $error)
        {
            $message .= "<p>";
            foreach ($error as $k => $e)
            {
                $message .= "<b>{$k}:</b> {$e}<br>";
            }
            $message .= "-----</p>";
        }

        $message .= "<h3>Raw Reason Phrase</h3><p>{$rawReasonPhrase}</p>";
        $message .= "<h3>Exception Trace</h3>
        <p>{$exception->getTraceAsString()}</p>
        ";

        $response = new Response();
        $response->setContent($message);
        $response->setStatusCode($statusCode);

        $event->setResponse($response);
    }

}