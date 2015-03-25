<?php
namespace Everyman\Neo4j;

/**
 * Represents a relationship between two nodes
 */
class Relationship extends PropertyContainer
{
	const DirectionAll       = 'all';
	const DirectionIn        = 'in';
	const DirectionOut       = 'out';

    /**
     * create or get the relation
     */
    const UNIQUE_GET_CREATE  = 'get_or_create';

    /**
     * create relation or get an error
     */
    const UNIQUE_CREATE_FAIL = 'create_or_fail';

	/**
	 * @var Node Our start node
	 */
	protected $start = null;
	/**
	 * @var Node Our end node
	 */
	protected $end = null;
	/**
	 * @var string Our type
	 */
	protected $type = null;
    /**
     * @var bool unique relation
     */
    protected $unique = false;
    /**
     * @var string unique relation 'action'
     */
    protected $uniqueAction = self::UNIQUE_GET_CREATE;


	/**
	 * @inheritdoc
	 * @param Client $client
	 * @return Relationship
	 */
	public function setClient(Client $client)
	{
		parent::setClient($client);
		// set the client of our start and end nodes if they exists and doesn't have client yet
		if ($this->start && !$this->start->getClient()) {
			$this->start->setClient($client);
		}
		if ($this->end && !$this->end->getClient()) {
			$this->end->setClient($client);
		}
		return $this;
	}

	/**
	 * Delete this relationship
	 *
	 * @return PropertyContainer
	 * @throws Exception on failure
	 */
	public function delete()
	{
		$this->client->deleteRelationship($this);
		return $this;
	}

	/**
	 * Get the end node
	 *
	 * @return Node
	 */
	public function getEndNode()
	{
		if (null === $this->end) {
			$this->loadProperties();
		}
		return $this->end;
	}

	/**
	 * Get the start node
	 *
	 * @return Node
	 */
	public function getStartNode()
	{
		if (null === $this->start) {
			$this->loadProperties();
		}
		return $this->start;
	}

	/**
	 * Get the relationship type
	 *
	 * @return string
	 */
	public function getType()
	{
		$this->loadProperties();
		return $this->type;
	}

    /**
     * set the unique relation key
     *
     * @param string $key
     *
     * @return Relationship
     */
    public function setUniqueKey($key)
    {
        if (!is_array($this->unique)) {
            $this->unique = array('key' => null, 'value' => null);
        }

        $this->unique['key'] = $key;

        return $this;
    }

    /**
     * set the unique relation value
     *
     * @param string $value
     *
     * @return Relationship
     */
    public function setUniqueValue($value)
    {
        if (!is_array($this->unique)) {
            $this->unique = array('key' => null, 'value' => null);
        }

        $this->unique['value'] = $value;

        return $this;
    }

    /**
     * get the unique relation key
     *
     * @return string
     */
    public function getUniqueKey()
    {
        if (!is_array($this->unique)) {
            return null;
        }

        return $this->unique['key'];
    }

    /**
     * get the unique relation value
     *
     * @return string
     */
    public function getUniqueValue()
    {
        if (!is_array($this->unique)) {
            return null;
        }

        return $this->unique['value'];
    }

    /**
     * is this a unique relation (relevant at creation time)
     *
     * @return bool
     */
    public function isUnique()
    {
        return is_array($this->unique) && isset($this->unique['key']);
    }

    /**
     * reset the uniqueness of the relation
     * 'unique relation' -> false
     *
     * @return Relationship
     */
    public function resetUniqueness()
    {
        $this->unique = false;

        return $this;
    }

    /**
     * set the unique 'action'
     * - get_or_create
     * - create_or_fail
     *
     * @param string $action
     *
     * @return bool successfull
     */
    public function setUniqueAction($action)
    {
        if (in_array($action, array(
            self::UNIQUE_GET_CREATE,
            self::UNIQUE_CREATE_FAIL
        ))) {
            $this->uniqueAction = $action;

            return true;
        }

        return false;
    }

    /**
     * get the path appendix get_or_create / create_or_fail
     *
     * @return string
     */
    public function getUniqueAction()
    {
        return $this->uniqueAction;
    }

	/**
	 * Load this relationship
	 *
	 * @return PropertyContainer
	 * @throws Exception on failure
	 */
	public function load()
	{
		$this->client->loadRelationship($this);
		return $this;
	}

	/**
	 * Save this node
	 *
	 * @return PropertyContainer
	 * @throws Exception on failure
	 */
	public function save()
	{
		$this->client->saveRelationship($this);
		$this->useLazyLoad(false);
		return $this;
	}

	/**
	 * Set the end node
	 *
	 * @param Node $end
	 * @return Relationship
	 */
	public function setEndNode(Node $end)
	{
		$this->end = $end;
		return $this;
	}

	/**
	 * Set the start node
	 *
	 * @param Node $start
	 * @return Relationship
	 */
	public function setStartNode(Node $start)
	{
		$this->start = $start;
		return $this;
	}

	/**
	 * Set the type
	 *
	 * @param string $type
	 * @return Relationship
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}


	/**
	 * Be sure to add our properties to the things to serialize
	 *
	 * @return array
	 */
	public function __sleep()
	{
		return array_merge(parent::__sleep(), array('start', 'end', 'type'));
	}
}
