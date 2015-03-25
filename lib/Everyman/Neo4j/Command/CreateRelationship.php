<?php
namespace Everyman\Neo4j\Command;

use Everyman\Neo4j\Command,
	Everyman\Neo4j\Client,
	Everyman\Neo4j\Exception,
	Everyman\Neo4j\Relationship;

/**
 * Create a relationship
 */
class CreateRelationship extends Command
{
	protected $rel = null;

	/**
	 * Set the relationship to drive the command
	 *
	 * @param Client $client
	 * @param Relationship $rel
	 */
	public function __construct(Client $client, Relationship $rel)
	{
		parent::__construct($client);
		$this->rel = $rel;
	}

	/**
	 * Return the data to pass
	 *
	 * @return mixed
	 */
	protected function getData()
	{
        $start = $this->rel->getStartNode();
		$end = $this->rel->getEndNode();
		$type = $this->rel->getType();
		if (!$end || !$end->hasId()) {
			throw new Exception('No relationship end node specified');
		} else if (!$type) {
			throw new Exception('No relationship type specified');
		}

        $startUri = $this->getTransport()->getEndpoint().'/node/'.$start->getId();
        $endUri = $this->getTransport()->getEndpoint().'/node/'.$end->getId();
        $data = array('type' => $type);
        if ($this->rel->getUniqueKey() != null) {
            $data['key'] = $this->rel->getUniqueKey();
            $data['value'] = $this->rel->getUniqueValue();
            $data['start'] = $startUri;
            $data['end'] = $endUri;
        } else {
            $data['to'] = $endUri;
        }

        $properties = $this->rel->getProperties();
        if ($properties) {
            $data['data'] = $properties;
        }

		return $data;
	}

	/**
	 * Return the transport method to call
	 *
	 * @return string
	 */
	protected function getMethod()
	{
		return 'post';
	}

	/**
	 * Return the path to use
	 *
	 * @return string
	 */
	protected function getPath()
	{
		$start = $this->rel->getStartNode();
		if (!$start || !$start->hasId()) {
			throw new Exception('No relationship start node specified');
		}
        $path = '/node/'.$start->getId().'/relationships';

        if ($this->rel->isUnique()) {
            $path = '/index/relationship/'.$this->rel->getType();
            $path .= '?uniqueness=' . $this->rel->getUniqueAction();
        }

        return $path;
	}

	/**
	 * Use the results
	 *
	 * @param integer $code
	 * @param array   $headers
	 * @param array   $data
	 * @return boolean true on success
	 * @throws Exception on failure
	 */
	protected function handleResult($code, $headers, $data)
	{
		if ((int)($code / 100) != 2) {
			$this->throwException('Unable to create relationship', $code, $headers, $data);
		}

        //$uri = '';
        if (isset($headers['Location'])) {
            $uri = $headers['Location'];
        } else {
            $uri = $data['body']['self'];
        }
        $relId = $this->getEntityMapper()->getIdFromUri($uri);
		$this->rel->setId($relId);
		$this->getEntityCache()->setCachedEntity($this->rel);
		return true;
	}
}
