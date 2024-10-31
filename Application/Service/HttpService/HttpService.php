<?php

namespace Slackr\Application\Service\HttpService;

if (!defined('ABSPATH'))
{
    exit;
}

use Slackr\Application\Model\HttpResponseModel;
use Slackr\Application\Service\IService;

class HttpService implements IService
{
    public function __construct()
    {
    }

    /**
     * @param $url
     * @param string $data
     * @param null $extraHeader
     * @return HttpResponseModel
     */
    public function postRequest($url, $data, $extraHeader = null)
    {
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        if(null !== $extraHeader && is_array($extraHeader))
        {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $extraHeader);
        }

        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $responseModel = new HttpResponseModel();
        $responseModel->statusCode = $statusCode;
        $responseModel->response = $response;

        return $responseModel;
    }

    /**
     * @param $url
     * @param null $extraHeader
     * @return HttpResponseModel
     */
    public function getRequest($url, $extraHeader = null)
    {
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        if(null !== $extraHeader && is_array($extraHeader))
        {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $extraHeader);
        }

        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $responseModel = new HttpResponseModel();
        $responseModel->statusCode = $statusCode;
        $responseModel->response = $response;

        return $responseModel;
    }
}