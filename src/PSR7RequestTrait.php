<?php

namespace Bindto;

use Liuggio\Filler\PropertyTrait;
use Psr\Http\Message\ServerRequestInterface;

trait PSR7RequestTrait
{
    use PropertyTrait;

    public function fillPropertiesFromPSR7Request(ServerRequestInterface $request, &$to = null, $name = '')
    {
        $data = $this->_extractDataPSR7($request, $name);
        $this->fillProperties($data, $to);
    }

    private function _extractDataPSR7(ServerRequestInterface $request = null, $name = '')
    {
        $method = $request->getMethod();
        $queryParams = $request->getQueryParams();
        if ('GET' === $method) {
            if ('' === $name) {
                return $queryParams;
            }
            // Don't submit GET requests if the form's name does not exist
            // in the request
            if (!isset($queryParams[$name])) {
                return;
            }

            return $queryParams[$name];
        }

        $serverParams = $request->getServerParams();
        $uploadedFiles = $request->getUploadedFiles();
        if ('' === $name) {
            return $this->mergeParamsAndUploadedFiles($serverParams, $uploadedFiles);
        }

        if (isset($serverParams[$name]) || isset($uploadedFiles[$name])) {
            $default = null;
            $params = isset($serverParams[$name]) ? $serverParams[$name] : null;
            $files = isset($uploadedFiles[$name]) ? $uploadedFiles[$name] : null;

            return $this->mergeParamsAndUploadedFiles($params, $files);
        }

        // Don't submit the form if it is not present in the request
        return;
    }

    /**
     * @param $params
     * @param $files
     *
     * @return array
     */
    private function mergeParamsAndUploadedFiles($params, $files)
    {
        if (is_array($params) && is_array($files)) {
            $data = array_replace_recursive($params, $files);

            return $data;
        }

        $data = $params ?: $files;

        return $data;
    }
}
