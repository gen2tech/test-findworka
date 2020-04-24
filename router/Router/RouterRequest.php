<?php
/**
 * @ Package: Router - simple router class for php
 * @ Class: RouterRequest
 * @Author : Ogunyemi Oludayo / @gen2wind <ogunyemioludayo@gmail.com>
 * @Web    : https://github.com/gen2wind
 * @URL    : https://github.com/gen2wind/php-router
 * @Licence: The MIT License (MIT) - Copyright (c) - http://opensource.org/licenses/MIT
 */

namespace Inf\Router;

class RouterRequest
{
    /**
     * @var string $validMethods Valid methods for Requests
     */
    public static $validMethods = 'GET|POST|PUT|DELETE|HEAD|OPTIONS|PATCH|ANY|AJAX|XPOST|XPUT|XDELETE|XPATCH';

    /**
     * Request method validation
     *
     * @param string $data
     * @param string $method
     *
     * @return bool
     */
    public static function validMethod($data, $method)
    {
        $valid = false;
        if (strstr($data, '|')) {
            foreach (explode('|', $data) as $value) {
                $valid = self::checkMethods($value, $method);
                if ($valid) {
                    break;
                }
            }
        } else {
            $valid = self::checkMethods($data, $method);
        }

        return $valid;
    }

    /**
     * Get the request method used, taking overrides into account
     *
     * @return string
     */
    public static function getRequestMethod()
    {
        // Take the method as found in $_SERVER
        $method = $_SERVER['REQUEST_METHOD'];
        // If it's a HEAD request override it to being GET and prevent any output, as per HTTP Specification
        // @url http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
        if ($method === 'HEAD') {
            ob_start();
            $method = 'GET';
        } elseif ($method === 'POST') {
            $headers = self::getRequestHeaders();
            if (isset($headers['X-HTTP-Method-Override']) &&
                in_array($headers['X-HTTP-Method-Override'], ['PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD'])) {
                $method = $headers['X-HTTP-Method-Override'];
            } elseif (! empty($_POST['_method'])) {
                $method = strtoupper($_POST['_method']);
            }
        }

        return $method;
    }

    /**
     * check method valid
     *
     * @param string $value
     * @param string $method
     *
     * @return bool
     */
    protected static function checkMethods($value, $method)
    {
        if (in_array($value, explode('|', self::$validMethods))) {
            if (self::isAjax() && $value === 'AJAX') {
                return true;
            }

            if (self::isAjax() && strpos($value, 'X') === 0 && $method === ltrim($value, 'X')) {
                return true;
            }

            if (in_array($value, [$method, 'ANY'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check ajax request
     *
     * @return bool
     */
    protected static function isAjax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
    }

    /**
     * Get all request headers
     *
     * @return array
     */
    protected static function getRequestHeaders()
    {
        // If getallheaders() is available, use that
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        // Method getallheaders() not available: manually extract 'm
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_' || $name === 'CONTENT_TYPE' || $name === 'CONTENT_LENGTH') {
                $headerKey = str_replace(
                    [' ', 'Http'],
                    ['-', 'HTTP'],
                    ucwords(strtolower(str_replace('_', ' ', substr($name, 5))))
                );
                $headers[$headerKey] = $value;
            }
        }

        return $headers;
	}
	

	
	/**
	 * Fetch an item from the GET array
	 *
	 * @param	mixed	$index		Index for item to be fetched from $_GET
	 * @param	bool	$xss_clean	Whether to apply XSS filtering
	 * @return	mixed
	 */
	public static function getData($index = NULL, $xss_clean = FALSE)
	{
		return Self::_fetch_from_array($_GET, $index, $xss_clean);
	}



    /**
	 * Fetch an item from the POST array
	 *
	 * @param	mixed	$index		Index for item to be fetched from $_POST
	 * @param	bool	$xss_clean	Whether to apply XSS filtering
	 * @return	mixed
	 */
    public static function postData($index = NULL, $xss_clean = FALSE)
    {
        return Self::_fetch_from_array($_POST, $index, $xss_clean);
    }

    /**
	 * Fetch an item from the COOKIE array
	 *
	 * @param	mixed	$index		Index for item to be fetched from $_COOKIE
	 * @param	bool	$xss_clean	Whether to apply XSS filtering
	 * @return	mixed
	 */
	public static function cookieData($index = NULL, $xss_clean = FALSE)
	{
		return Self::_fetch_from_array($_COOKIE, $index, $xss_clean);
    }
    
    
	/**
	 * Fetch an item from the SERVER array
	 *
	 * @param	mixed	$index		Index for item to be fetched from $_SERVER
	 * @param	bool	$xss_clean	Whether to apply XSS filtering
	 * @return	mixed
	 */
	public static function serverData($index, $xss_clean = FALSE)
	{
		return Self::_fetch_from_array($_SERVER, $index, $xss_clean);
	}



    /**
	 * Fetch from array
	 *
	 * Internal method used to retrieve values from global arrays.
	 *
	 * @param	array	&$array		$_GET, $_POST, $_COOKIE, $_SERVER, etc.
	 * @param	mixed	$index		Index for item to be fetched from $array
	 * @param	bool	$xss_clean	Whether to apply XSS filtering
	 * @return	mixed
	 */
	private static function _fetch_from_array(&$array, $index = NULL, $xss_clean = FALSE)
	{
		// If $index is NULL, it means that the whole $array is requested
		isset($index) OR $index = array_keys($array);

		// allow fetching multiple keys at once
		if (is_array($index))
		{
			$output = array();
			foreach ($index as $key)
			{
				$output[$key] = Self::_fetch_from_array($array, $key, $xss_clean);
			}

			return $output;
		}

		if (isset($array[$index]))
		{
			$value = $array[$index];
		}
		elseif (($count = preg_match_all('/(?:^[^\[]+)|\[[^]]*\]/', $index, $matches)) > 1) // Does the index contain array notation
		{
			$value = $array;
			for ($i = 0; $i < $count; $i++)
			{
				$key = trim($matches[0][$i], '[]');
				if ($key === '') // Empty notation will return the value as array
				{
					break;
				}

				if (isset($value[$key]))
				{
					$value = $value[$key];
				}
				else
				{
					return NULL;
				}
			}
		}
		else
		{
			return NULL;
		}

		return ($xss_clean === TRUE) ? Self::xss_clean($value) : $value;
    }
    


    /*
    * XSS filter 
    *
    * This was built from numerous sources
    * (thanks all, sorry I didn't track to credit you)
    * 
    * It was tested against *most* exploits here: http://ha.ckers.org/xss.html
    * WARNING: Some weren't tested!!!
    * Those include the Actionscript and SSI samples, or any newer than Jan 2011
    *
    *
    * TO-DO: compare to SymphonyCMS filter:
    * https://github.com/symphonycms/xssfilter/blob/master/extension.driver.php
    * (Symphony's is probably faster than my hack)
    */
   
   
    private static function xss_clean($data)
   {
           // Fix &entity\n;
           $data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
           $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
           $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
           $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
   
           // Remove any attribute starting with "on" or xmlns
           $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
   
           // Remove javascript: and vbscript: protocols
           $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
           $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
           $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
   
           // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
           $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
           $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
           $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);
   
           // Remove namespaced elements (we do not need them)
           $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
   
           do
           {
                   // Remove really unwanted tags
                   $old_data = $data;
                   $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
           }
           while ($old_data !== $data);
   
           // we are done...
           return $data;
   }



   /**
	 * Fetch the IP Address
	 *
	 * Determines and validates the visitor's IP address.
	 *
	 * @return	string	IP address
	 */
	public static function ip_address($proxy_ips = [])
	{
        
		if ( ! empty($proxy_ips) && ! is_array($proxy_ips))
		{
			$proxy_ips = explode(',', str_replace(' ', '', $proxy_ips));
		}

		$ip_address = Self::serverData('REMOTE_ADDR');

		if ($proxy_ips)
		{
			foreach (array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP') as $header)
			{
				if (($spoof = Self::serverData($header)) !== NULL)
				{
					// Some proxies typically list the whole chain of IP
					// addresses through which the client has reached us.
					// e.g. client_ip, proxy_ip1, proxy_ip2, etc.
					sscanf($spoof, '%[^,]', $spoof);

					if ( ! Self::valid_ip($spoof))
					{
						$spoof = NULL;
					}
					else
					{
						break;
					}
				}
			}

			if ($spoof)
			{
				for ($i = 0, $c = count($proxy_ips); $i < $c; $i++)
				{
					// Check if we have an IP address or a subnet
					if (strpos($proxy_ips[$i], '/') === FALSE)
					{
						// An IP address (and not a subnet) is specified.
						// We can compare right away.
						if ($proxy_ips[$i] === $ip_address)
						{
							$ip_address = $spoof;
							break;
						}

						continue;
					}

					// We have a subnet ... now the heavy lifting begins
					isset($separator) OR $separator = Self::valid_ip($ip_address, 'ipv6') ? ':' : '.';

					// If the proxy entry doesn't match the IP protocol - skip it
					if (strpos($proxy_ips[$i], $separator) === FALSE)
					{
						continue;
					}

					// Convert the REMOTE_ADDR IP address to binary, if needed
					if ( ! isset($ip, $sprintf))
					{
						if ($separator === ':')
						{
							// Make sure we're have the "full" IPv6 format
							$ip = explode(':',
								str_replace('::',
									str_repeat(':', 9 - substr_count($ip_address, ':')),
									$ip_address
								)
							);

							for ($j = 0; $j < 8; $j++)
							{
								$ip[$j] = intval($ip[$j], 16);
							}

							$sprintf = '%016b%016b%016b%016b%016b%016b%016b%016b';
						}
						else
						{
							$ip = explode('.', $ip_address);
							$sprintf = '%08b%08b%08b%08b';
						}

						$ip = vsprintf($sprintf, $ip);
					}

					// Split the netmask length off the network address
					sscanf($proxy_ips[$i], '%[^/]/%d', $netaddr, $masklen);

					// Again, an IPv6 address is most likely in a compressed form
					if ($separator === ':')
					{
						$netaddr = explode(':', str_replace('::', str_repeat(':', 9 - substr_count($netaddr, ':')), $netaddr));
						for ($j = 0; $j < 8; $j++)
						{
							$netaddr[$j] = intval($netaddr[$j], 16);
						}
					}
					else
					{
						$netaddr = explode('.', $netaddr);
					}

					// Convert to binary and finally compare
					if (strncmp($ip, vsprintf($sprintf, $netaddr), $masklen) === 0)
					{
						$ip_address = $spoof;
						break;
					}
				}
			}
		}

		if ( ! Self::valid_ip($ip_address))
		{
			return $ip_address = '0.0.0.0';
		}

		return $ip_address;
    }
    

    
	/**
	 * Validate IP Address
	 *
	 * @param	string	$ip	IP address
	 * @param	string	$which	IP protocol: 'ipv4' or 'ipv6'
	 * @return	bool
	 */
	private static function valid_ip($ip, $which = '')
	{
		switch (strtolower($which))
		{
			case 'ipv4':
				$which = FILTER_FLAG_IPV4;
				break;
			case 'ipv6':
				$which = FILTER_FLAG_IPV6;
				break;
			default:
				$which = NULL;
				break;
		}

		return (bool) filter_var($ip, FILTER_VALIDATE_IP, $which);
	}
   
}
