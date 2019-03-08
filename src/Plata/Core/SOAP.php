<?php

/**
 *  \details &copy; 2019 Open Ximdex Evolution SL [http://www.ximdex.org]
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

namespace Plata\Core;

class SOAP
{    
    private $version;
    private $url;
    private $exceptions;
    private $trace;
    private $timeout;
    private $soap;
    private $memoryLimit;

    public function __construct()
    {
        $configs = Config::getGroup('SOAP');
        $this->version = $configs['VERSION'];
        $this->exceptions = $configs['EXCEPTIONS'];
        $this->trace = $configs['TRACE'];
        $this->timeout = $configs['TIMEOUT'];
        $this->memoryLimit = $configs['MEMORY_LIMIT'];
    }

    public function setVersion(string $version)
    {
        $this->version = $version;
        return $this;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setUrl(string $url)
    {
        $this->url = $url;
        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function call(string $call, array $parameters = [])
    {
        $options = [
            'soap_version' => $this->version,
            'exceptions' => $this->exceptions,
            'trace'=> $this->trace,
            'connection_timeout' => $this->timeout
        ];
        ini_set('memory_limit', $this->memoryLimit);
        $this->soap = new \SoapClient($this->url, $options);
        return $this->soap->$call($parameters);
    }
}
