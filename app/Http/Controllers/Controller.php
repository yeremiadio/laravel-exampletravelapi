<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use ImageKit\ImageKit;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function responseSuccess($msg, $arr = null, $status = 200)
    {
        $res = [
            'status' => true,
            'message' => ($msg == "") ? "Success" : $msg,
        ];

        if ($arr) {
            $res['data'] = $arr;
        }

        return response()->json($res, $status);
    }

    protected function responseFailed($msg = null, $arr = null, $status = 500)
    {
        $res = [
            'status' => false,
            'message' => (!$msg) ? "Error" : $msg,
        ];

        if ($arr) {
            $res['data'] = $arr;
        }

        return response()->json($res, $status);
    }

    protected function uploadFileImageKit($name = 'file')
    {
        if ($this->request->hasFile($name)) {
            $file = $this->request->file($name);
            $client = new ImageKit(
                env('IMAGEKIT_PUBLIC_KEY'),
                env('IMAGEKIT_PRIVATE_KEY'),
                env('IMAGEKIT_CLIENTID')
            );

            $response = $client->upload(array(
                'file' => base64_encode(file_get_contents($file)),
                'fileName' => $file->getClientOriginalName()
            ));
            // echo ("Upload URL" . json_encode($response->result));
            $url = $response->result->url;
            return $url;
        } else {
            $client = new ImageKit(
                env('IMAGEKIT_PUBLIC_KEY'),
                env('IMAGEKIT_PRIVATE_KEY'),
                env('IMAGEKIT_CLIENTID')
            );

            $response = $client->uploadFile(array(
                'file' => $name,
                'fileName' => "my_file_name.jpg",
            ));

            $url = $response->result->url;
            return $url;
        }
    }
    protected function deleteFileImageKit($url)
    {
        if ($url) {
            $client = new ImageKit(
                env('IMAGEKIT_PUBLIC_KEY'),
                env('IMAGEKIT_PRIVATE_KEY'),
                env('IMAGEKIT_CLIENTID')
            );
            // Parse the URL
            $parsedUrl = parse_url($url);

            // Extract the path
            $path = $parsedUrl['path'];

            // Explode the path by "/"
            $pathParts = explode('/', $path);

            // Get the value you're interested in
            $fileName = $pathParts[2];
            $listFiles = $client->listFiles(array(
                "searchQuery" => "name=$fileName",
            ));
            //  echo(json_encode($listFiles));
            $fileId = $listFiles->result[0]->fileId;
            $client->deleteFile($fileId);
            // echo($response);
            // $url = $response->result->url;
            // return $url;
        }
    }
}
