<?php
/**
 *  \details &copy; 2011  Open Ximdex Evolution SL [http://www.ximdex.org]
 *
 *  Ximdex a Semantic Content Management System (CMS)
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published
 *  by the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  See the Affero GNU General Public License for more details.
 *  You should have received a copy of the Affero GNU General Public License
 *  version 3 along with Ximdex (see LICENSE file).
 *
 *  If not, visit http://gnu.org/licenses/agpl-3.0.html.
 *
 * @author Ximdex DevTeam <dev@ximdex.com>
 * @version $Revision$
 */

require_once 'vendor/autoload.php';

use core\SOAP;
use core\Config;

class Plata
{
    protected $configs;
    protected $toTranslate;
    protected $string;
    protected $to;
    protected $from;
    protected $type;

    private $soap;

    const CALL = 'translate_string';

    const TYPE_TXT = 'txt';
    const TYPE_HTML = 'html';
    //const TYPE_HTML_PLAIN = 'html-string';
    const TYPE_XML = 'xml';
    //const TYPE_WXML = 'wxml';

    const PLATA_ERRORS =  [
        1 => 'Excepción de tipo genérico',
        2 => 'Error en el formato del fichero',
        3 => 'Sentido de traducción no soportado o incorrecto',
        4 => 'Error en la validación del fichero a traducir',
        5 => 'Usuario incorrecto, el SW no es capaz de validarlo o el usuario no existe',
        6 => 'Error de conversión. Se ha producido un error en el proceso de conversión del fichero a taducir.',
        7 => 'Error en el valor del checksum. No coincide el valor enviado con el calculado.',
        8 => 'Error en la escritura del fichero traducido',
        9 => 'Error en la ejecución del comando de traducción. El servicio podría estar caído. Contactar con el administrador del sistema.',
        10 => 'Error en la conversión a base64 del fichero',
        11 => 'Error en el cálculo del valor MD5',
        12 => 'Error en la manipulación del fichero a traducir',
        13 => 'Error al almacenar el contenido traducido en la caché',
        14 => 'Error al incorporar el mensaje del disclaimer '
    ];

    public function __construct(
        string $string,
        string $to,
        string $from = 'es',
        string $type = self::TYPE_TXT,
        array $configs = null
    ) {
        if (is_null($configs)) {
            $configs = Config::rejectGroup('SOAP');
            foreach ($configs as $key => $config) {
                if (array_key_exists('LANGS', $config)) {
                    $configs[$key]['LANGS'] = explode('|', $config['LANGS']);
                }
            }
        }

        $this->soap = new SOAP();

        $this->setString($string)
            ->setTo($to)
            ->setFrom($from)
            ->setType($type)
            ->setConfigs($configs);
    }

    public function setConfigs(array $configs)
    {
        $this->configs = $configs;
        return $this;
    }

    public function getConfigs() : array
    {
        return $this->configs;
    }

    public function setString(string $string) : self
    {
        $this->string = $string;
        return $this;
    }

    public function getString(): string
    {
        return $this->string;
    }

    public function setTo(string $to) : self
    {
        $this->to = $to;
        return $this;
    }

    public function getTo() : string
    {
        return $this->to;
    }

    public function setFrom(string $from) : self
    {
        $this->from = $from;
        return $this;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setType(string $type) : self
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function translate(string $route = '')
    {
        $this->prepareString();
        $service = $this->getService();
        $routes = $service['routes'];
        unset($service['routes']);

        $_route = $routes['default'];
        if($route !== '') {
            $_route = $route;
        }

        $status = 'fail';
        $message = 'ROUTE option in config is required';

        if(!is_null($routes)) {
            try {
                $response = $this->soap
                    ->setUrl($_route)
                    ->call(static::CALL, $service);
                // $response = new \stdClass();
                // $response->return = $this->toTranslate;

                $status = 'ok';
                $message = $response->return;

                if(is_integer($response->return) && array_key_exists($response->return, static::PLATA_ERRORS)) {
                    $status = 'fail';
                    $message = static::PLATA_ERRORS[$response->return];
                }

            } catch (SoapFault $e) {
                $message = $e->getMessage();
                if($e->faultcode === 'WSDL' && $route === ''){
                    $result = $this->translate($routes['fallback']);
                    $status = $result['status'];
                    $message = $result['message'];
                }
            }
        }

        return $this->response($status, $message);
    }

    private function getService()
    {
        $service = null;
        foreach ($this->configs as $config) {
            if (array_search($this->to, $config['LANGS'])) {
                $service = [
                    'routes' => [
                        'default' => $config['ROUTE'],
                        'fallback' => $config['MIRROR'],
                    ],             
                    'user' => $config['USER'],
                    'key' => $config['PASSWORD'],
                    'type' => $this->getType(),
                    'markUnknown' => '',
                    'direction' => $this->from.'-'.$this->to,
                    'string' => $this->toTranslate
                ];
            }
        }
        return $service;
    }

    private function prepareString()
    {
        $method = 'prepare'.strtoupper($this->type);
        $this->toTranslate = $this->string;
        if (method_exists($this, $method)) {
            $this->toTranslate = $this->$method();
        }
    }

    private function prepareHTML()
    {
        return '
        <!DOCTYPE html>
        <html>
            <head>
                <meta charset="utf-8">
            </head>
            <body>'
                .$this->string.
            '</body>
        </html>
        ';
    }

    private function prepareXML()
    {
        return '<root>'.$this->string.'</root>';
    }

    private function response($status, $message) {
        return [
            'status' => $status,
            'message' => $message
        ];
    }
}
