<?php

namespace Pantheon\Terminus\Models;

use Pantheon\Terminus\Config\Config;
use Terminus\Models\Collections\UserOrganizationMemberships;
use Pantheon\Terminus\Models\TerminusModel;
use Terminus\Models\Collections\Instruments;
use Terminus\Models\Collections\MachineTokens;
use Terminus\Models\Collections\SshKeys;
use Pantheon\Terminus\Models\Collections\Workflows;
use Terminus\Session;

class User extends TerminusModel
{
    /**
     * @var UserOrganizationMemberships
     */
    public $organizations;

    /**
     * @var Instruments
     */
    protected $instruments;

    /**
     * @var Instruments
     */
    protected $machine_tokens;

    /**
     * @var SshKeys
     */
    protected $ssh_keys;

    /**
     * @var Workflows
     */
    protected $workflows;
    protected $cache;

    /**
     * @var \stdClass
     * @todo Wrap this in a proper class.
     */
    private $aliases;

    /**
     * @var \stdClass
     * @todo Wrap this in a proper class.
     */
    private $profile;

    /**
     * Object constructor
     *
     * @param object $attributes Attributes of this model
     * @param array $options Options to set as $this->key
     */
    public function __construct(\stdClass $attributes = null, array $options = array())
    {
        parent::__construct($attributes, $options);
        $this->cache = $this->getContainer()->get('FileCache');
        $this->setFetchUrl(sprintf('users/%s', $this->id));
        $data = $this->fetch();
        $this->attributes = $data->attributes;
        $this->workflows = $data->workflows;
        $this->instruments = $data->instruments;
        $this->machine_tokens = $data->machine_tokens;
        $this->ssh_keys = $data->ssh_keys;
        $this->organizations = $data->organizations;
        $this->getCache()->putData('user', $this);
    }

    public static function findOrCreate($id)
    {
        $userCache = Config::getContainer()->get('FileCache')->getData('User');
        if (property_exists($userCache, 'id')
            && $userCache->id == $id) {
            return $userCache;

        }
        return new User((object)['id' => $id]);
    }

    /**
     * @return \stdClass
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param \stdClass $profile
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
    }

    /**
     * @return mixed
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param mixed $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * Modify response data between fetch and assignment
     *
     * @param [object] $data attributes received from API response
     * @return [object] $data
     */
    public function parseAttributes($data)
    {
        if (isset($data->profile)) {
            $this->profile = $data->profile;
        }
        return $data;
    }

    /**
     * Retrieves drush aliases for this user
     *
     * @return \stdClass
     */
    public function getAliases()
    {
        if (!$this->aliases) {
            $this->setAliases();
        }
        return $this->aliases;
    }

    /**
     * Retrieves organization data for this user
     *
     * @return Organization[]
     */
    public function getOrganizations()
    {
        $organizations = $this->organizations->all();
        return $organizations;
    }

    /**
     * Requests API data and returns an object of user site data
     *
     * @param string $organization UUID of organization to requests sites from,
     *   or null to fetch for all organizations.
     * @return \stdClass
     */
    public function getSites($organization = null)
    {
        $path = sprintf('users/%s', $this->id);
        if ($organization) {
            $path .= sprintf('/organizations/%s/memberships/sites',
              $organization);
        } else {
            $path .= '/sites';
        }
        $options = ['method' => 'get',];
        $response = $this->request->request($path, $options);
        return $response['data'];
    }

    /**
     * Formats User object into an associative array for output
     *
     * @return [array] $data associative array of data for output
     */
    public function serialize()
    {
        $first_name = $last_name = null;
        if (isset($this->profile->firstname)) {
            $first_name = $this->profile->firstname;
        }
        if (isset($this->profile->lastname)) {
            $last_name = $this->profile->lastname;
        }

        $data = array(
          'firstname' => $first_name,
          'lastname' => $last_name,
          'email' => $this->get('email'),
          'id' => $this->id,
        );
        return $data;
    }

    /**
     * Requests API data and populates $this->aliases
     *
     * @return void
     */
    private function setAliases()
    {
        $path = sprintf('users/%s/drush_aliases', $this->id);
        $options = ['method' => 'get',];
        $response = $this->request->request($path, $options);

        $this->aliases = $response['data']->drush_aliases;
    }

    private function setFetchUrl($url)
    {
        $this->fetchUrl = $url;
    }

}
