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

namespace Plata\Core;

class SOAP
{
    private $version;
    private $url;
    private $exceptions;
    private $trace;

    public function __construct()
    {
        $configs = Config::getGroup('SOAP');
        $this->version = $configs['VERSION'];
        $this->exceptions = $configs['EXCEPTIONS'];
        $this->trace = $configs['TRACE'];
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

    public function call($call, array $options = []) {
        if(!array_key_exists('soap_version', $options)) {
            $options['soap_version'] = $this->version;
        }

        $client = new \SoapClient($this->url);
        return $client->__soapCall($call, $options);
    }
}
