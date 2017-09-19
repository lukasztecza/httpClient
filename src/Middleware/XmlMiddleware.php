<?php
namespace HttpClient\Middleware;

use HttpClient\Middleware\MiddlewareAbstract;

class XmlMiddleware extends MiddlewareAbstract
{
    public function process(array $curlOptionsArray) : array
    {
        if ($this->options[self::PARAMETER_ENCODE_REQUEST]) {
            $curlOptionsArray[CURLOPT_HTTPHEADER][] = 'Content-Type: application/xml';
            if (isset($curlOptionsArray[CURLOPT_POSTFIELDS])) {
                $curlOptionsArray[CURLOPT_POSTFIELDS] = $this->buildXmlPayload(
                    $curlOptionsArray[CURLOPT_POSTFIELDS],
                    $this->options[self::PARAMETER_ROOT_NODE]
                );
            }
        }

        $response = $this->next->process($curlOptionsArray);
        if (!empty($response['body'])) {
            $xml = new \SimpleXMLElement($response['body']);
            $response['body'] = json_decode(json_encode($xml), true);
        }
        return $response;
    }

    private function buildXmlPayload(array $payload, string $rootNode) : string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $rootNode . '/>');
        $this->addXmlFromArray($xml, $payload);
        return $xml->asXml();
    }

    private function addXmlFromArray(&$xml, $payload) {
        foreach($payload as $key => $value) {
            if(is_array($value)) {
                if(!is_numeric($key)){
                    $subnode = $xml->addChild($key);
                } else {
                    $subnode = $xml->addChild('item_' . $key);
                }
                $this->addXmlFromArray($subnode, $value);
            } else {
                if(!is_numeric($key)) {
                    $xml->addChild($key, $value);
                } else {
                    $xml->addChild('item_' . $key, $value);
                }
            }
        }
    }
}
