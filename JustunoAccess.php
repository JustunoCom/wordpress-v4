<?php
	class ju4_JustunoAccess{
		private $apiKey;
		private $domain;
		private $email;
		private $apiEndpointUrl;
		private $guid;
		private $password;
		
		public function __construct($settings){
			$this->apiKey = $settings['apiKey'];
			$this->domain = $settings['domain'];
			$this->email = $settings['email'];		
			$this->guid = isset($settings['guid']) ? $settings['guid'] : null;
			$this->password = isset($settings['password']) ? $settings['password'] : null;
			$this->apiEndpointUrl = 'https://www.justuno.com/api/endpoint.html';
		}
		
		public function getWidgetConfig(){
            // Check if the cURL extension is loaded
            if(!extension_loaded("curl")){
                throw new ju4_JustunoAccessException('Plug-in requires php `curl` extension which seems to be not activated on this server. Please activate it.');
            }
        
            $params = array(
                'key' => $this->apiKey,
                'email' => $this->email,
                'domain' => $this->domain,
                'action' => 'install'
            );
        
            if (isset($this->password)) {
                $params['password'] = $this->password;
            }
        
            // Build the query URL
            $url = add_query_arg($params, $this->apiEndpointUrl);
        
            // Make the request
            $response = wp_remote_get($url, array(
                'sslverify' => false
            ));
        
            // Check for errors
            if (is_wp_error($response)) {
                throw new ju4_JustunoAccessException('Request error: ' . esc_attr($response->get_error_message()));
            }
        
            $body = wp_remote_retrieve_body($response);
        
            // Parse the XML response
            $dom = new DOMDocument;
            try {
                $dom->loadXML($body);
            } catch (Exception $e) {
                throw new ju4_JustunoAccessException('Failed to parse XML response: ' .esc_attr($e->getMessage()));
            }
        
            $nodes = $dom->getElementsByTagName('result');
            if (!$nodes || $nodes->length == 0) {
                throw new ju4_JustunoAccessException('Incorrect response from remote server');
            }
        
            if ($nodes->item(0)->nodeValue == 0) {
                $nodes = $dom->getElementsByTagName('error');
                throw new ju4_JustunoAccessException(esc_attr($nodes->item(0)->nodeValue));
            }
        
            $justunoConf = array();
        
            $nodes = $dom->getElementsByTagName('guid');
            if ($nodes && $nodes->length !== 0) {
                $this->guid = $justunoConf['guid'] = $nodes->item(0)->nodeValue;
            }
        
            $nodes = $dom->getElementsByTagName('embed');
            if ($nodes && $nodes->length !== 0) {
                $justunoConf['embed'] = $nodes->item(0)->nodeValue;
            }
        
            $nodes = $dom->getElementsByTagName('conversion');
            if ($nodes && $nodes->length !== 0) {
                $justunoConf['conversion'] = $nodes->item(0)->nodeValue;
            }
        
            return $justunoConf;
        }
        
		
		public function getDashboardLink(){
            $params = array(
                'key' => $this->apiKey,
                'email' => $this->email,
                'domain' => $this->domain,
                'action' => 'login',
                'guid' => $this->guid
            );
        
            if (isset($this->password)) {
                $params['password'] = $this->password;
            }
        
            // Build the query URL
            $url = add_query_arg($params, $this->apiEndpointUrl);
        
            // Make the request
            $response = wp_remote_get($url, array(
                'sslverify' => false
            ));
        
            // Check for errors
            if (is_wp_error($response)) {
                throw new ju4_JustunoAccessException('Request error: ' . esc_attr($response->get_error_message()));
            }
        
            $body = wp_remote_retrieve_body($response);
        
            // Parse the XML response
            $dom = new DOMDocument;
            try {
                $dom->loadXML($body);
            } catch (Exception $e) {
                throw new ju4_JustunoAccessException('Failed to parse XML response: ' . esc_attr($e->getMessage()));
            }
        
            $nodes = $dom->getElementsByTagName('result');
            if (!$nodes || $nodes->length == 0) {
                throw new ju4_JustunoAccessException('Incorrect response from remote server');
            }
        
            if ($nodes->item(0)->nodeValue == 0) {
                $nodes = $dom->getElementsByTagName('error');
                throw new ju4_JustunoAccessException(esc_attr($nodes->item(0)->nodeValue));
            }
        
            $nodes = $dom->getElementsByTagName('secure_login_url');
            if ($nodes && $nodes->length !== 0) {
                return $nodes->item(0)->nodeValue;
            }
        
            throw new ju4_JustunoAccessException('No secure login URL found in response');
        }        
	}
	
	class ju4_JustunoAccessException extends Exception{
		
	}
