<?php

class XmlReader {
    private $xml;
    private $filepath;

    /**
     * Constructor that accepts the XML file path
     * 
     * @param string $filepath Path to the XML file
     * @throws Exception If file doesn't exist or is not readable
     */
    public function __construct(string $filepath) {
        if (!file_exists($filepath)) {
            throw new Exception("XML file not found: {$filepath}");
        }
        
        if (!is_readable($filepath)) {
            throw new Exception("XML file is not readable: {$filepath}");
        }

        $this->filepath = $filepath;
        $this->loadXml();
    }

    /**
     * Loads the XML file
     * 
     * @throws Exception If XML parsing fails
     */
    private function loadXml(): void {
        $this->xml = @simplexml_load_string(file_get_contents($this->filepath));
        
        if ($this->xml === false) {
            throw new Exception('Failed to parse XML file');
        }
    }

    /**
     * Gets a value from XML using a path notation (e.g., "credentials/token")
     * 
     * @param string $path Path to the XML element using forward slashes
     * @return string|null The value of the element or null if not found
     */
    public function getValue(string $path): ?string {
        $parts = explode('/', $path);
        $current = $this->xml;

        foreach ($parts as $part) {
            if (!isset($current->$part)) {
                return null;
            }
            $current = $current->$part;
        }

        return (string)$current;
    }

    /**
     * Gets multiple values from XML as an associative array
     * 
     * @param array $paths Array of paths to retrieve
     * @return array Associative array of path => value pairs
     */
    public function getValues(array $paths): array {
        $results = [];
        foreach ($paths as $path) {
            $results[$path] = $this->getValue($path);
        }
        return $results;
    }

    /**
     * Gets all values from a specific XML node
     * 
     * @param string $nodePath Path to the parent node
     * @return array|null Array of child elements or null if node not found
     */
    public function getNodeValues(string $nodePath): ?array {
        $node = $this->getValue($nodePath);
        if ($node === null) {
            return null;
        }

        $parts = explode('/', $nodePath);
        $current = $this->xml;

        foreach ($parts as $part) {
            if (!isset($current->$part)) {
                return null;
            }
            $current = $current->$part;
        }

        $result = [];
        foreach ($current as $key => $value) {
            $result[$key] = (string)$value;
        }

        return $result;
    }

    /**
     * Checks if a path exists in the XML
     * 
     * @param string $path Path to check
     * @return bool True if path exists, false otherwise
     */
    public function pathExists(string $path): bool {
        $parts = explode('/', $path);
        $current = $this->xml;

        foreach ($parts as $part) {
            if (!isset($current->$part)) {
                return false;
            }
            $current = $current->$part;
        }

        return true;
    }
}