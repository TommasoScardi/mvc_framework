<?php

namespace MvcFramework\Controllers;

use MvcFramework\Core\Controller;
use MvcFramework\Core\Request;
use MvcFramework\Core\Response;

class FileController extends Controller
{

    public function Index(Request $req, Response $res)
    {
        $res->end("upload handled on Upload action");
    }

    public function Upload(Request $req, Response $res)
    {
        $req->registerMethods(Request::METHOD_POST);
        $fileName = $req->saveFileUpload("fileToUpload", "test", ["jpg", "png"]);
        if ($fileName)
        {
            $res->end("file uploaded successfully, name => $fileName");
            return;
        }
        $res->end("error uploading the file");
    }
}
