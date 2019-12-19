<?php


namespace Ling\Light_MicroPermission\Service;


use Ling\BabyYaml\BabyYamlUtil;
use Ling\Light\ServiceContainer\LightServiceContainerInterface;
use Ling\Light_User\LightUserInterface;
use Ling\Light_UserManager\Service\LightUserManagerService;

/**
 * The LightMicroPermissionService class.
 */
class LightMicroPermissionService
{

    /**
     * This property holds the container for this instance.
     * @var LightServiceContainerInterface
     */
    protected $container;

    /**
     * This property holds the microPermissionsMap for this instance.
     * It's an array of micro-permission => (array of) permissions.
     *
     * @var array
     */
    protected $microPermissionsMap;

    /**
     * This property holds the disabledNamespaces for this instance.
     * @var array
     */
    protected $disabledNamespaces;


    /**
     * Builds the LightMicroPermissionService instance.
     */
    public function __construct()
    {
        $this->container = null;
        $this->microPermissionsMap = [];
        $this->disabledNamespaces = [];
    }

    /**
     * Sets the container.
     *
     * @param LightServiceContainerInterface $container
     */
    public function setContainer(LightServiceContainerInterface $container)
    {
        $this->container = $container;
    }


    /**
     * Disable the micro-permission system for the given namespace, so that the
     * hasMicroPermission method will always return true for all micro-permissions of that namespace.
     *
     * @param string $namespace
     */
    public function disableNamespace(string $namespace)
    {
        if (false === in_array($namespace, $this->disabledNamespaces, true)) {
            $this->disabledNamespaces[] = $namespace;
        }
    }

    /**
     * Restores all the disabled namespaces.
     */
    public function restoreNamespaces()
    {
        $this->disabledNamespaces = [];
    }


    /**
     * Register the micro-permission bindings defined in the given file.
     * See more details in the @page(micro-permission conception notes).
     *
     * @param string $file
     */
    public function registerMicroPermissionsByFile(string $file)
    {
        $this->microPermissionsMap = array_merge_recursive($this->microPermissionsMap, BabyYamlUtil::readFile($file));
    }


    /**
     * Returns whether the current user has the given micro-permission.
     *
     * @param string $microPermission
     * @return bool
     * @throws \Exception
     */
    public function hasMicroPermission(string $microPermission): bool
    {
        if ($this->disabledNamespaces) {
            $p = explode(".", $microPermission);
            $namespace = array_shift($p);
            if (in_array($namespace, $this->disabledNamespaces, true)) {
                return true;
            }
        }

        /**
         * @var $userManager LightUserManagerService
         */
        $userManager = $this->container->get("user_manager");
        /**
         * @var $user LightUserInterface
         */
        $user = $userManager->getUser();
        if (true === $user->hasRight("*")) {
            return true;
        }

        if (array_key_exists($microPermission, $this->microPermissionsMap)) {
            $permissions = $this->microPermissionsMap[$microPermission];
            if (false === is_array($permissions)) {
                $permissions = [$permissions];
            }
            foreach ($permissions as $permission) {
                if (true === $user->hasRight($permission)) {
                    return true;
                }
            }
        }
        return false;
    }
}