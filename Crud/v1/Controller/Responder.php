<?php
namespace Dot\Crud\Running\Controller;

use Dot\Crud\Running\Record\ErrorCode;
use Dot\Crud\Running\Record\Document\ErrorDocument;
use Dot\Crud\System\Response;

class Responder {

    public function error($error = null, $argument = null, $details = null) {
        $errorCode = new ErrorCode($error);
        $status = $errorCode->getStatus();
        $document = new ErrorDocument($errorCode, $argument, $details);
        return new Response($status, $document);
    }

    public function success($result)    {
        return new Response(Response::OK, $result);
    }

}